<script lang="ts">
  import { fly, fade } from 'svelte/transition';
  import { onMount, onDestroy } from 'svelte';
  import { browser } from '$app/environment';
  import type { PageData } from './$types';
  
  export let data: PageData;
  
  let visible = false;
  let currentDate = new Date();
  let currentMonth = currentDate.getMonth();
  let currentYear = currentDate.getFullYear();
  
  // Month and year picker state
  let showMonthPicker = false;
  let showYearPicker = false;
  
  // Get events from server data
  let events = data.events || [];
  
  // Reactive statement to get upcoming events sorted chronologically
  $: upcomingEvents = (() => {
    const now = new Date();
    const today = now.toISOString().split('T')[0]; // Get YYYY-MM-DD format
    
    return events
      .filter(event => event.date >= today) // Only future/today events
      .sort((a, b) => {
        // Sort by date first
        const dateComparison = a.date.localeCompare(b.date);
        if (dateComparison !== 0) return dateComparison;
        
        // If dates are the same, sort by start time
        // Extract start time from the time string (format: "HH:MM AM/PM - HH:MM AM/PM")
        const getStartTime = (timeStr: string) => {
          const match = timeStr.match(/^(\d{1,2}:\d{2} [AP]M)/);
          if (!match) return 0; // Return 0 instead of the string for invalid formats
          
          const [time, modifier] = match[1].split(' ');
          let [hours, minutes] = time.split(':').map(Number);
          
          if (modifier === 'PM' && hours !== 12) hours += 12;
          if (modifier === 'AM' && hours === 12) hours = 0;
          
          return hours * 60 + minutes; // Convert to minutes for easy comparison
        };
        
        return getStartTime(a.time) - getStartTime(b.time);
      });
  })();
  
  const months = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
  ];
  
  const daysOfWeek = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
  
  function getDaysInMonth(month: number, year: number) {
    return new Date(year, month + 1, 0).getDate();
  }
  
  function getFirstDayOfMonth(month: number, year: number) {
    return new Date(year, month, 1).getDay();
  }
  
  function getEventsForDate(date: string) {
    return events
      .filter(event => event.date === date)
      .sort((a, b) => {
        // Sort events within the day by start time
        const getStartTime = (timeStr: string) => {
          const match = timeStr.match(/^(\d{1,2}:\d{2} [AP]M)/);
          if (!match) return 0;
          
          const [time, modifier] = match[1].split(' ');
          let [hours, minutes] = time.split(':').map(Number);
          
          if (modifier === 'PM' && hours !== 12) hours += 12;
          if (modifier === 'AM' && hours === 12) hours = 0;
          
          return hours * 60 + minutes;
        };
        
        return getStartTime(a.time) - getStartTime(b.time);
      });
  }
  
  function previousMonth() {
    if (currentMonth === 0) {
      currentMonth = 11;
      currentYear--;
    } else {
      currentMonth--;
    }
  }
  
  function nextMonth() {
    if (currentMonth === 11) {
      currentMonth = 0;
      currentYear++;
    } else {
      currentMonth++;
    }
  }
  
  function selectMonth(monthIndex: number) {
    currentMonth = monthIndex;
    showMonthPicker = false;
  }
  
  function selectYear(year: number) {
    currentYear = year;
    showYearPicker = false;
  }
  
  function toggleMonthPicker() {
    showMonthPicker = !showMonthPicker;
    showYearPicker = false;
  }
  
  function toggleYearPicker() {
    showYearPicker = !showYearPicker;
    showMonthPicker = false;
  }
  
  // Generate year range (current year ± 10 years)
  function getYearRange() {
    const currentYearActual = new Date().getFullYear();
    const years = [];
    for (let i = currentYearActual - 10; i <= currentYearActual + 10; i++) {
      years.push(i);
    }
    return years;
  }
  
  function formatDate(year: number, month: number, day: number) {
    return `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
  }
  
  // Check admin login state from cookies
  let isAdminLoggedIn = false;
  
  // Cookie checking interval
  let cookieCheckInterval: NodeJS.Timeout | null = null;
  
  // Real-time updates for events
  let updateInterval: NodeJS.Timeout | null = null;
  let isUpdating = false;
  
  // Notification functions
  let notificationMessage = '';
  let notificationType: 'success' | 'error' = 'success';
  let showNotification = false;
  
  function showSuccessNotification(message: string) {
    notificationMessage = message;
    notificationType = 'success';
    showNotification = true;
    setTimeout(() => showNotification = false, 3000);
  }
  
  function showErrorNotification(message: string) {
    notificationMessage = message;
    notificationType = 'error';
    showNotification = true;
    setTimeout(() => showNotification = false, 5000);
  }
  
  // Event form state
  let showEventForm = false;
  let editingEvent: typeof events[0] | null = null;
  let isCreatingNew = false;
  let eventForm = {
    title: '',
    description: '',
    event_date: '',
    start_time: '',
    end_time: '',
    location: '',
    event_type: 'event',
    organizer: ''
  };
  
  // Event modal state
  let selectedEvent: typeof events[0] | null = null;
  let showEventModal = false;
  let hoveredEvent: typeof events[0] | null = null;
  let showTooltip = false;
  let tooltipPosition = { x: 0, y: 0, showAbove: false };
  let hoveredEventElement: HTMLElement | null = null;
  
  // Day events modal state
  let showDayEventsModal = false;
  let selectedDayEvents: typeof events = [];
  let selectedDateForModal: string | null = null;
  
  function handleEventClick(event: typeof events[0]) {
    selectedEvent = event;
    showEventModal = true;
  }
  
  function closeEventModal() {
    showEventModal = false;
    selectedEvent = null;
  }
  
  function handleMoreClick(date: string, dayEvents: typeof events) {
    selectedDayEvents = dayEvents;
    selectedDateForModal = date;
    showDayEventsModal = true;
  }

  function closeDayEventsModal() {
    showDayEventsModal = false;
    selectedDayEvents = [];
    selectedDateForModal = null;
  }
  
  function handleEventHover(event: typeof events[0], mouseEvent: MouseEvent) {
    // Don't show tooltips on mobile/touch devices
    if (window.matchMedia('(hover: none)').matches || 'ontouchstart' in window) {
      return;
    }
    
    const target = mouseEvent.target as HTMLElement;
    const rect = target.getBoundingClientRect();
    
    hoveredEvent = event;
    showTooltip = true;
    hoveredEventElement = target;
    
    // Simple positioning: just use mouse position but ensure it stays in bounds
    const mouseX = mouseEvent.clientX;
    const mouseY = mouseEvent.clientY;
    
    const tooltipWidth = 320; // w-80 = 320px
    const tooltipHeight = 200; // Estimated max height
    const margin = 20;
    
    // Calculate position relative to mouse
    let x = mouseX + 15; // Offset to the right of cursor
    let y = mouseY - 10; // Slightly above cursor
    
    // Adjust horizontal position if tooltip would go off screen
    if (x + tooltipWidth > window.innerWidth - margin) {
      x = mouseX - tooltipWidth - 15; // Position to the left of cursor
    }
    if (x < margin) {
      x = margin;
    }
    
    // Adjust vertical position if tooltip would go off screen
    if (y + tooltipHeight > window.innerHeight - margin) {
      y = window.innerHeight - tooltipHeight - margin;
    }
    if (y < margin) {
      y = margin;
    }
    
    tooltipPosition = { 
      x: x,
      y: y,
      showAbove: false // Don't show arrow for cursor-following tooltip
    };
  }
  
  function handleEventLeave() {
    // Don't process leave events on mobile/touch devices
    if (window.matchMedia('(hover: none)').matches || 'ontouchstart' in window) {
      return;
    }
    
    hoveredEvent = null;
    showTooltip = false;
    hoveredEventElement = null;
    tooltipPosition = { x: 0, y: 0, showAbove: false };
  }
  
  // Handle click outside to close tooltip and modal
  function handleDocumentClick(event: MouseEvent) {
    const target = event.target as HTMLElement;
    
    // Close month/year pickers if clicking outside
    if (showMonthPicker && !target.closest('.month-picker') && !target.closest('.month-toggle')) {
      showMonthPicker = false;
    }
    if (showYearPicker && !target.closest('.year-picker') && !target.closest('.year-toggle')) {
      showYearPicker = false;
    }
    
    // Close tooltip if clicking outside (only on non-mobile devices)
    if (showTooltip && hoveredEventElement && !window.matchMedia('(hover: none)').matches && !('ontouchstart' in window)) {
      if (!hoveredEventElement.contains(target) && !target.closest('.tooltip')) {
        handleEventLeave();
      }
    }
    
    // Handle modal closing - prioritize the topmost modal
    if (showEventForm && !target.closest('.event-form-modal') && !target.closest('[data-edit-trigger]')) {
      // If event form is open, only close it, don't touch the event modal
      // Added check for edit button to prevent immediate closing when clicking edit
      hideEventForm();
    } else if (showDayEventsModal && !target.closest('.day-events-modal')) {
      closeDayEventsModal();
    } else if (showEventModal && !showEventForm && !target.closest('.event-modal') && !target.closest('[data-event-trigger]')) {
      // Only close event modal if event form is not open
      closeEventModal();
    }
  }
  
  // Real-time update functions for events
  async function fetchLatestEvents() {
    if (isUpdating) return;
    
    try {
      isUpdating = true;
      const response = await fetch('/api/events');
      if (response.ok) {
        const data = await response.json();
        events = data.events || [];
      }
    } catch (error) {
      console.error('Error fetching latest events:', error);
    } finally {
      isUpdating = false;
    }
  }
  
  function startRealTimeUpdates() {
    if (browser && !updateInterval) {
      // Poll for updates every 3 seconds when admin is logged in
      updateInterval = setInterval(fetchLatestEvents, 3000);
    }
  }
  
  function stopRealTimeUpdates() {
    if (updateInterval) {
      clearInterval(updateInterval);
      updateInterval = null;
    }
  }
  
  // Function to check admin login status from cookies
  function checkAdminStatus() {
    if (browser) {
      const authCookie = document.cookie
        .split('; ')
        .find(row => row.startsWith('auth='));
      const newAdminStatus = !!authCookie;
      
      if (newAdminStatus !== isAdminLoggedIn) {
        isAdminLoggedIn = newAdminStatus;
        console.log('Admin status changed:', isAdminLoggedIn);
      }
    }
  }
  
  // Start checking for cookie changes periodically
  function startCookieCheck() {
    if (browser && !cookieCheckInterval) {
      cookieCheckInterval = setInterval(checkAdminStatus, 500); // Check every 500ms
    }
  }
  
  // Stop checking for cookie changes
  function stopCookieCheck() {
    if (cookieCheckInterval) {
      clearInterval(cookieCheckInterval);
      cookieCheckInterval = null;
    }
  }
  
  // Admin logout
  function handleAdminLogout() {
    isAdminLoggedIn = false;
    stopRealTimeUpdates();
    document.cookie = "auth=; path=/; expires=Thu, 01 Jan 1970 00:00:00 GMT"; // Delete cookie
    window.location.reload();
  }
  
  // Reactive statement to manage real-time updates
  $: if (isAdminLoggedIn) {
    startRealTimeUpdates();
  } else {
    stopRealTimeUpdates();
  }

  // Handle event form submission
  function showEventFormModal() {
    editingEvent = null;
    isCreatingNew = true;
    eventForm = {
      title: '',
      description: '',
      event_date: '',
      start_time: '',
      end_time: '',
      location: '',
      event_type: 'event',
      organizer: ''
    };
    showEventForm = true;
  }
  
  function editEvent(event: typeof events[0]) {
    editingEvent = event;
    isCreatingNew = false;
    
    // Parse the time string to get start and end times
    const timeMatch = event.time.match(/(\d{1,2}:\d{2} [AP]M) - (\d{1,2}:\d{2} [AP]M)/);
    let startTime = '';
    let endTime = '';
    
    if (timeMatch) {
      // Convert 12-hour format to 24-hour format for HTML time input
      startTime = convertTo24Hour(timeMatch[1]);
      endTime = convertTo24Hour(timeMatch[2]);
    }
    
    eventForm = {
      title: event.title,
      description: event.description,
      event_date: event.date,
      start_time: startTime,
      end_time: endTime,
      location: event.location,
      event_type: event.type,
      organizer: event.organizer
    };
    showEventForm = true;
  }
  
  function convertTo24Hour(time12h: string): string {
    const [time, modifier] = time12h.split(' ');
    let [hours, minutes] = time.split(':');
    if (hours === '12') {
      hours = '00';
    }
    if (modifier === 'PM') {
      hours = (parseInt(hours, 10) + 12).toString();
    }
    return `${hours.padStart(2, '0')}:${minutes}`;
  }
  
  async function deleteEvent(eventId: number) {
    if (!confirm('Are you sure you want to delete this event?')) return;
    
    try {
      const response = await fetch(`/api/events/${eventId}`, { method: 'DELETE' });
      if (response.ok) {
        showSuccessNotification('Event deleted successfully!');
        setTimeout(() => window.location.reload(), 1000);
        closeEventModal();
      } else {
        console.error('Failed to delete event');
        showErrorNotification('Failed to delete event. Please try again.');
      }
    } catch (error) {
      console.error('Error deleting event:', error);
      showErrorNotification('An error occurred while deleting the event.');
    }
  }
  
  function hideEventForm() {
    showEventForm = false;
    editingEvent = null;
    // Reset form
    eventForm = {
      title: '',
      description: '',
      event_date: '',
      start_time: '',
      end_time: '',
      location: '',
      event_type: 'event',
      organizer: ''
    };
  }
  
  async function handleEventSubmit() {
    try {
      const method = isCreatingNew ? 'POST' : 'PUT';
      const url = isCreatingNew ? '/api/events' : `/api/events/${editingEvent?.id}`;
      
      const response = await fetch(url, {
        method,
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(eventForm),
      });

      if (response.ok) {
        // Reset form
        eventForm = {
          title: '',
          description: '',
          event_date: '',
          start_time: '',
          end_time: '',
          location: '',
          event_type: 'event',
          organizer: ''
        };
        showEventForm = false;
        
        // Faculty-style refresh: reload the page
        showSuccessNotification(isCreatingNew ? 'Event created successfully!' : 'Event updated successfully!');
        setTimeout(() => window.location.reload(), 1000);
      } else {
        console.error(`Failed to ${isCreatingNew ? 'create' : 'update'} event`);
        showErrorNotification(`Failed to ${isCreatingNew ? 'create' : 'update'} event. Please try again.`);
      }
    } catch (error) {
      console.error(`Error ${isCreatingNew ? 'creating' : 'updating'} event:`, error);
      showErrorNotification(`An error occurred while ${isCreatingNew ? 'creating' : 'updating'} the event.`);
    }
  }
  
  onMount(() => {
    visible = true;
    
    // Check admin status from cookie initially
    checkAdminStatus();
    
    // Start checking for cookie changes periodically
    startCookieCheck();
    
    // Add document click listener for click-outside functionality
    document.addEventListener('click', handleDocumentClick);
    
    // Cleanup function
    return () => {
      document.removeEventListener('click', handleDocumentClick);
    };
  });
  
  onDestroy(() => {
    stopRealTimeUpdates();
    stopCookieCheck();
  });
</script>

<svelte:head>
  <title>School Calendar - New Cabalan National High School</title>
</svelte:head>

<!-- Main container with background image and pattern overlay -->
<div class="relative min-h-screen bg-cover bg-center font-sans" style="background-image: url('/ncnhs.jpg'); background-attachment: scroll;">
  <!-- Modern Gradient Overlay with Pattern -->
  <div class="absolute inset-0 bg-gradient-to-b from-green-900/40 via-green-800/20 to-yellow-500/30 mix-blend-overlay"></div>

  <!-- Page Content (positioned above overlay) -->
  <div class="relative z-10 flex min-h-screen flex-col">
    
    <!-- Main Content Area -->
    <main class="flex flex-grow flex-col items-center pb-0">
      <!-- School Logo -->
      {#if visible}
      <div class="my-2 md:my-4" in:fade={{ duration: 1000 }}>
        <div class="flex h-32 w-32 md:h-40 md:w-40 items-center justify-center">
          <img src="/logo.png" alt="School Logo" class="h-full w-full object-contain drop-shadow-lg" />
        </div>
      </div>
      {/if}
      
      <!-- Calendar Container -->
      {#if visible}
      <div class="w-full max-w-6xl mx-auto mb-8 flex flex-col" in:fly={{ y: 100, duration: 800, delay: 600 }}>
        <!-- Header Section -->
        <div class="rounded-t-2xl bg-gradient-to-r from-green-600 to-green-700 text-white py-4 shadow-lg">
          <div class="flex justify-between items-center px-6">
            <!-- Left side: Empty spacer -->
            <div class="flex-1"></div>
            
            <!-- Center: Title -->
            <h2 class="text-xl md:text-2xl lg:text-3xl font-bold md:pl-3 pr-4 md:pr-0">SCHOOL CALENDAR</h2>
            
            <!-- Right side: Admin controls -->
            <div class="flex-1 flex justify-end ml-2 sm:ml-4 md:ml-0">
              {#if isAdminLoggedIn}
                <div class="flex items-center gap-2">
                  {#if isUpdating}
                    <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin" title="Syncing data..."></div>
                  {/if}
                  <button
                    on:click={showEventFormModal}
                    class="bg-white/20 hover:bg-white/30 text-white px-3 py-1 rounded-lg text-sm font-medium transition-colors flex items-center gap-1"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Add Event
                  </button>
                  <button 
                    on:click={handleAdminLogout}
                    class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded-lg text-sm font-medium transition-colors"
                  >
                    Logout
                  </button>
                </div>
              {/if}
            </div>
          </div>
        </div>
        
        <!-- Main Content Box with Gradient Border -->
        <div class="bg-gradient-to-r from-green-600 to-green-700 px-3 pt-3 pb-5 shadow-2xl min-h-[70vh] rounded-b-xl">
          <div class="bg-white p-4 md:p-6 rounded-xl h-full flex flex-col">
            
            <!-- Calendar Header -->
            <div class="flex justify-between items-center mb-6 flex-shrink-0">
              <button 
                on:click={previousMonth}
                class="p-2 rounded-lg bg-green-600 text-white hover:bg-green-700 transition-colors"
                aria-label="Previous month"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
              </button>
              
              <!-- Month and Year Display with Pickers -->
              <div class="relative flex items-center gap-2">
                <!-- Month Picker -->
                <div class="relative">
                  <button
                    on:click={toggleMonthPicker}
                    class="text-2xl md:text-3xl font-bold text-green-800 hover:text-green-600 transition-colors cursor-pointer month-toggle"
                  >
                    {months[currentMonth]}
                  </button>
                  
                  {#if showMonthPicker}
                    <div class="absolute top-full left-1/2 transform -translate-x-1/2 mt-2 bg-white border border-gray-200 rounded-lg shadow-lg z-50 p-3 grid grid-cols-3 gap-2 min-w-[280px] month-picker" transition:fly={{ y: -10, duration: 200 }}>
                      {#each months as month, index}
                        <button
                          on:click={() => selectMonth(index)}
                          class="px-3 py-2 text-sm rounded-md hover:bg-green-100 transition-colors whitespace-nowrap {currentMonth === index ? 'bg-green-600 text-white' : 'text-gray-700'}"
                        >
                          {month.slice(0, 3)}
                        </button>
                      {/each}
                    </div>
                  {/if}
                </div>
                
                <!-- Year Picker -->
                <div class="relative">
                  <button
                    on:click={toggleYearPicker}
                    class="text-2xl md:text-3xl font-bold text-green-800 hover:text-green-600 transition-colors cursor-pointer year-toggle"
                  >
                    {currentYear}
                  </button>
                  
                  {#if showYearPicker}
                    <div class="absolute top-full left-1/2 transform -translate-x-1/2 mt-2 bg-white border border-gray-200 rounded-lg shadow-lg z-50 p-3 max-h-48 overflow-y-auto min-w-[120px] year-picker" transition:fly={{ y: -10, duration: 200 }}>
                      {#each getYearRange() as year}
                        <button
                          on:click={() => selectYear(year)}
                          class="block w-full text-center px-3 py-2 text-sm rounded-md hover:bg-green-100 transition-colors {currentYear === year ? 'bg-green-600 text-white' : 'text-gray-700'}"
                        >
                          {year}
                        </button>
                      {/each}
                    </div>
                  {/if}
                </div>
              </div>
              
              <button 
                on:click={nextMonth}
                class="p-2 rounded-lg bg-green-600 text-white hover:bg-green-700 transition-colors"
                aria-label="Next month"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
              </button>
            </div>
            
            <!-- Scrollable Content Area -->
            <div class="flex-1 overflow-y-auto pr-2">
              <!-- Calendar Grid -->
              <div class="grid grid-cols-7 gap-1 md:gap-2 mb-6">
                <!-- Days of week header -->
                {#each daysOfWeek as day}
                  <div class="p-2 md:p-3 bg-green-100 text-green-800 font-semibold text-center rounded-lg">
                    {day}
                  </div>
                {/each}
                
                <!-- Empty cells for days before month starts -->
                {#each Array(getFirstDayOfMonth(currentMonth, currentYear)) as _}
                  <div class="p-2 md:p-4 h-20 md:h-24"></div>
                {/each}
                
                <!-- Days of the month -->
                {#each Array(getDaysInMonth(currentMonth, currentYear)) as _, i}
                  {@const day = i + 1}
                  {@const dateString = formatDate(currentYear, currentMonth, day)}
                  {@const dayEvents = getEventsForDate(dateString)}
                  {@const isToday = 
                    currentYear === new Date().getFullYear() && 
                    currentMonth === new Date().getMonth() && 
                    day === new Date().getDate()
                  }
                  
                  <div class="p-1 md:p-2 h-20 md:h-24 border border-gray-200 rounded-lg hover:bg-green-50 transition-colors overflow-hidden relative {isToday ? 'bg-yellow-100 border-yellow-400' : 'bg-white'}">
                    <div class="text-sm md:text-base font-medium text-green-800 mb-1">
                      {day}
                    </div>
                    
                    <!-- Events for this day -->
                    <div class="space-y-1 overflow-hidden">
                      {#each dayEvents.slice(0, 2) as event, index}
                        <div 
                          class="text-xs p-1 rounded truncate cursor-pointer transition-all duration-200 {
                            event.type === 'meeting' ? 'bg-blue-100 text-blue-800 hover:bg-blue-200' :
                            event.type === 'event' ? 'bg-purple-100 text-purple-800 hover:bg-purple-200' :
                            'bg-green-100 text-green-800 hover:bg-green-200'
                          }"
                          data-event-trigger
                          on:click={() => handleEventClick(event)}
                          on:mouseenter={(e) => handleEventHover(event, e)}
                          on:mouseleave={handleEventLeave}
                          on:keydown={(e) => e.key === 'Enter' && handleEventClick(event)}
                          tabindex="0"
                          role="button"
                          aria-label="View event details: {event.title}"
                        >
                          {event.title}
                        </div>
                      {/each}
                    </div>
                    
                    <!-- Small +n indicator in lower right corner -->
                    {#if dayEvents.length > 2}
                      <div class="absolute bottom-1 right-1 bg-blue-600 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center cursor-pointer hover:bg-blue-700 transition-colors shadow-sm group"
                           on:click={(e) => {
                             e.stopPropagation();
                             handleMoreClick(dateString, dayEvents);
                           }}
                           on:keydown={(e) => {
                             if (e.key === 'Enter') {
                               e.stopPropagation();
                               handleMoreClick(dateString, dayEvents);
                             }
                           }}
                           tabindex="0"
                           role="button"
                           aria-label="View {dayEvents.length - 2} more events for this day">
                        +{dayEvents.length - 2}
                        
                        <!-- Tooltip showing remaining events -->
                        <div class="absolute bottom-6 right-0 bg-gray-900 text-white text-xs rounded-lg p-2 min-w-max opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none z-10 shadow-lg">
                          <div class="font-semibold mb-1">{dayEvents.length - 2} more event{dayEvents.length - 2 === 1 ? '' : 's'}:</div>
                          {#each dayEvents.slice(2) as event}
                            <div class="truncate max-w-40">{event.title}</div>
                          {/each}
                          <!-- Tooltip arrow -->
                          <div class="absolute top-full right-2 w-0 h-0 border-l-2 border-r-2 border-t-4 border-l-transparent border-r-transparent border-t-gray-900"></div>
                        </div>
                      </div>
                    {/if}
                  </div>
                {/each}
              </div>
              
              <!-- Events Legend -->
              <div class="bg-gray-100 rounded-xl p-4 mb-6">
                <h4 class="text-lg font-semibold text-green-800 mb-3">Event Types</h4>
                <div class="flex flex-wrap gap-4">
                  <div class="flex items-center gap-2">
                    <div class="w-4 h-4 bg-blue-100 border border-blue-300 rounded"></div>
                    <span class="text-sm text-gray-700">Meetings</span>
                  </div>
                  <div class="flex items-center gap-2">
                    <div class="w-4 h-4 bg-purple-100 border border-purple-300 rounded"></div>
                    <span class="text-sm text-gray-700">School Events</span>
                  </div>
                  <div class="flex items-center gap-2">
                    <div class="w-4 h-4 bg-green-100 border border-green-300 rounded"></div>
                    <span class="text-sm text-gray-700">Academic</span>
                  </div>
                  <div class="flex items-center gap-2">
                    <div class="w-4 h-4 bg-yellow-100 border border-yellow-300 rounded"></div>
                    <span class="text-sm text-gray-700">Today</span>
                  </div>
                </div>
              </div>
              
              <!-- Upcoming Events -->
              <div class="bg-gray-50 rounded-xl p-4">
                <div class="flex justify-between items-center mb-3">
                  <h4 class="text-lg font-semibold text-green-800">These are 5 of the closest upcoming events: </h4>
                  {#if isAdminLoggedIn}
                    <button
                      on:click={showEventFormModal}
                      class="px-3 py-1 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700 transition-colors"
                    >
                      Add Event
                    </button>
                  {/if}
                </div>
                <div class="space-y-2">
                  {#each upcomingEvents.slice(0, 5) as event}
                    <div 
                      class="flex items-center gap-3 p-2 bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-200 cursor-pointer"
                      data-event-trigger
                      on:click={() => handleEventClick(event)}
                      on:mouseenter={(e) => handleEventHover(event, e)}
                      on:mouseleave={handleEventLeave}
                      on:keydown={(e) => e.key === 'Enter' && handleEventClick(event)}
                      tabindex="0"
                      role="button"
                      aria-label="View event details: {event.title}"
                    >
                      <div class="w-3 h-3 rounded-full {
                        event.type === 'meeting' ? 'bg-blue-500' :
                        event.type === 'event' ? 'bg-purple-500' :
                        'bg-green-500'
                      }"></div>
                      <span class="text-sm text-gray-600">{event.date}</span>
                      <span class="text-sm font-medium text-gray-800 flex-1">{event.title}</span>
                      <span class="text-xs text-gray-500">{event.time}</span>
                    </div>
                  {/each}
                </div>
              </div>
            </div>
            
          </div>
        </div>
        <!-- Green background extension to footer -->
        <div class="bg-gradient-to-r from-green-600 to-green-700 flex-grow"></div>
      </div>
      {/if}
    </main>
    
    <!-- Global Tooltip -->
    {#if showTooltip && hoveredEvent}
      <div 
        class="fixed bg-gray-900 text-white text-xs rounded-lg p-3 shadow-2xl pointer-events-none z-[9999] w-80 max-h-60 overflow-y-auto transition-opacity duration-200 tooltip hidden md:block"
        style="left: {tooltipPosition.x}px; top: {tooltipPosition.y}px;"
        in:fade={{ duration: 150 }}
        out:fade={{ duration: 150 }}
      >
        <div class="font-semibold mb-1">{hoveredEvent.title}</div>
        <div class="text-gray-300 mb-1">🕐 {hoveredEvent.time}</div>
        <div class="text-gray-300 mb-1">📍 {hoveredEvent.location}</div>
        <div class="text-gray-300 text-xs leading-relaxed">{hoveredEvent.description}</div>
        <div class="text-xs text-gray-400 mt-2 italic">Click for more details</div>
      </div>
    {/if}
    
    <!-- Day Events Modal -->
    {#if showDayEventsModal && selectedDateForModal}
      <div class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 pt-20 z-[99]" 
           transition:fade={{ duration: 200 }}
           on:click={closeDayEventsModal}
           on:keydown={(e) => e.key === 'Escape' && closeDayEventsModal()}>
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full max-h-[80vh] overflow-y-auto day-events-modal" 
             transition:fly={{ y: 50, duration: 300 }}
             on:click={(e) => e.stopPropagation()}>
          <!-- Modal Header -->
          <div class="bg-gradient-to-r from-green-600 to-green-700 text-white p-6 rounded-t-2xl">
            <div class="flex justify-between items-start">
              <div>
                <h3 class="text-xl font-bold mb-2">Events for {selectedDateForModal}</h3>
              </div>
              <button
                on:click={closeDayEventsModal}
                class="text-white hover:text-green-200 transition-colors p-1"
                aria-label="Close modal"
              >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>
          
          <!-- Modal Body -->
          <div class="p-6 space-y-3">
            {#each selectedDayEvents as event}
              <div 
                class="flex items-center gap-3 p-2 bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-200 cursor-pointer border"
                data-event-trigger
                on:click={(e) => { 
                  e.stopPropagation(); 
                  handleEventClick(event); 
                  closeDayEventsModal(); 
                }}
                on:keydown={(e) => {
                  if (e.key === 'Enter') {
                    e.stopPropagation();
                    handleEventClick(event);
                    closeDayEventsModal();
                  }
                }}
                tabindex="0"
                role="button"
                aria-label="View event details: {event.title}"
              >
                <div class="w-3 h-3 rounded-full {
                  event.type === 'meeting' ? 'bg-blue-500' :
                  event.type === 'event' ? 'bg-purple-500' :
                  'bg-green-500'
                }"></div>
                <div class="flex-1">
                  <span class="text-sm font-medium text-gray-800">{event.title}</span>
                  <span class="block text-xs text-gray-500">{event.time}</span>
                </div>
              </div>
            {/each}
          </div>
        </div>
      </div>
    {/if}

    <!-- Event Details Modal -->
    {#if showEventModal && selectedEvent}
      <div class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 pt-20 z-[110]" 
           transition:fade={{ duration: 200 }}
           on:click={closeEventModal}
           on:keydown={(e) => e.key === 'Escape' && closeEventModal()}>
        <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full max-h-[80vh] overflow-y-auto event-modal" 
             transition:fly={{ y: 50, duration: 300 }}
             on:click={(e) => e.stopPropagation()}>
          <!-- Modal Header -->
          <div class="bg-gradient-to-r from-green-600 to-green-700 text-white p-6 rounded-t-2xl">
            <div class="flex justify-between items-start">
              <div>
                <h3 class="text-xl font-bold mb-2">{selectedEvent.title}</h3>
                <div class="text-green-100 text-sm">
                  <div class="flex items-center gap-2 mb-1">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                    </svg>
                    {selectedEvent.date}
                  </div>
                  <div class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                    </svg>
                    {selectedEvent.time}
                  </div>
                </div>
              </div>
              <button
                on:click={closeEventModal}
                class="text-white hover:text-green-200 transition-colors p-1"
                aria-label="Close modal"
              >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>
          
          <!-- Modal Body -->
          <div class="p-6">
            <!-- Event Type Badge -->
            <div class="mb-4">
              <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {
                selectedEvent.type === 'meeting' ? 'bg-blue-100 text-blue-800' :
                selectedEvent.type === 'event' ? 'bg-purple-100 text-purple-800' :
                'bg-green-100 text-green-800'
              }">
                {selectedEvent.type === 'meeting' ? '🤝 Meeting' :
                 selectedEvent.type === 'event' ? '🎉 Event' :
                 '📚 Academic'}
              </span>
            </div>
            
            <!-- Location -->
            <div class="mb-4">
              <h4 class="font-semibold text-gray-700 mb-2 flex items-center gap-2">
                <svg class="w-5 h-5 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                </svg>
                Location
              </h4>
              <p class="text-gray-600 ml-7">{selectedEvent.location}</p>
            </div>
            
            <!-- Description -->
            <div class="mb-4">
              <h4 class="font-semibold text-gray-700 mb-2 flex items-center gap-2">
                <svg class="w-5 h-5 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z" clip-rule="evenodd" />
                </svg>
                Description
              </h4>
              <p class="text-gray-600 ml-7 leading-relaxed">{selectedEvent.description}</p>
            </div>
            
            <!-- Organizer -->
            <div class="mb-6">
              <h4 class="font-semibold text-gray-700 mb-2 flex items-center gap-2">
                <svg class="w-5 h-5 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                </svg>
                Organizer
              </h4>
              <p class="text-gray-600 ml-7">{selectedEvent.organizer}</p>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex gap-3">
              {#if isAdminLoggedIn && selectedEvent}
                <button
                  on:click={() => selectedEvent && editEvent(selectedEvent)}
                  class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors font-medium"
                  data-edit-trigger
                >
                  Edit
                </button>
                <button
                  on:click={() => selectedEvent && deleteEvent(selectedEvent.id)}
                  class="flex-1 bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors font-medium"
                >
                  Delete
                </button>
              {/if}
              <button
                on:click={closeEventModal}
                class="flex-1 bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 transition-colors font-medium"
              >
                Close
              </button>
            </div>
          </div>
        </div>
      </div>
    {/if}
    
    <!-- Add Event Form Modal -->
    {#if showEventForm}
      <div class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 pt-20 z-[100]" 
           transition:fade={{ duration: 200 }}
           on:click|self={hideEventForm}>
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full max-h-[80vh] overflow-y-auto event-form-modal" 
             transition:fly={{ y: 50, duration: 300 }}>
          <div class="bg-gradient-to-r from-green-600 to-green-700 text-white p-6 rounded-tl-2xl">
            <div class="flex justify-between items-center">
              <h3 class="text-xl font-bold">{isCreatingNew ? 'Add New Event' : 'Edit Event'}</h3>
              <button
                on:click={hideEventForm}
                class="text-white hover:text-green-200 transition-colors"
                aria-label="Close form"
              >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>
          
          <form on:submit|preventDefault={handleEventSubmit} class="p-6 space-y-4">
            <div>
              <label for="event-title" class="block text-sm font-medium text-gray-700 mb-2">Event Title</label>
              <input
                id="event-title"
                type="text"
                bind:value={eventForm.title}
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                placeholder="Enter event title"
              />
            </div>
            
            <div>
              <label for="event-description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
              <textarea
                id="event-description"
                bind:value={eventForm.description}
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                placeholder="Enter event description"
              ></textarea>
            </div>
            
            <div>
              <label for="event-date" class="block text-sm font-medium text-gray-700 mb-2">Event Date</label>
              <input
                id="event-date"
                type="date"
                bind:value={eventForm.event_date}
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
              />
            </div>
            
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label for="start-time" class="block text-sm font-medium text-gray-700 mb-2">Start Time</label>
                <input
                  id="start-time"
                  type="time"
                  bind:value={eventForm.start_time}
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                />
              </div>
              
              <div>
                <label for="end-time" class="block text-sm font-medium text-gray-700 mb-2">End Time</label>
                <input
                  id="end-time"
                  type="time"
                  bind:value={eventForm.end_time}
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                />
              </div>
            </div>
            
            <div>
              <label for="event-location" class="block text-sm font-medium text-gray-700 mb-2">Location</label>
              <input
                id="event-location"
                type="text"
                bind:value={eventForm.location}
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                placeholder="Enter event location"
              />
            </div>
            
            <div>
              <label for="event-type" class="block text-sm font-medium text-gray-700 mb-2">Event Type</label>
              <select
                id="event-type"
                bind:value={eventForm.event_type}
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
              >
                <option value="meeting">Meeting</option>
                <option value="event">School Event</option>
                <option value="academic">Academic</option>
              </select>
            </div>
            
            <div>
              <label for="event-organizer" class="block text-sm font-medium text-gray-700 mb-2">Organizer</label>
              <input
                id="event-organizer"
                type="text"
                bind:value={eventForm.organizer}
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                placeholder="Enter organizer name"
              />
            </div>
            
            <div class="flex gap-3 pt-4">
              <button
                type="button"
                on:click={hideEventForm}
                class="flex-1 bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 transition-colors"
              >
                Cancel
              </button>
              <button
                type="submit"
                class="flex-1 bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors"
              >
                {isCreatingNew ? 'Add Event' : 'Update Event'}
              </button>
            </div>
          </form>
        </div>
      </div>
    {/if}
    
    <!-- Success/Error Notification -->
    {#if showNotification}
      <div class="fixed top-4 right-4 z-50 max-w-sm" 
           in:fly={{ x: 300, duration: 300 }} 
           out:fly={{ x: 300, duration: 300 }}>
        <div class={`p-4 rounded-lg shadow-lg ${notificationType === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'}`}>
          <div class="flex items-center gap-2">
            {#if notificationType === 'success'}
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
            {:else}
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            {/if}
            <span class="font-medium">{notificationMessage}</span>
          </div>
        </div>
      </div>
    {/if}
    
    <!-- Modern Footer -->
    <footer class="bg-green-900/80 text-white py-3 md:py-4 backdrop-blur-md border-t-0 border-green-700/30 mt-0">
      <div class="container mx-auto text-center text-xs md:text-sm">
        <p>© {new Date().getFullYear()} New Cabalan National High School. All rights reserved.</p>
        <p class="mt-1 md:mt-2 text-yellow-300/80 text-[10px] md:text-xs">Empowering students through education since 1979</p>
      </div>
    </footer>
  </div>
</div>
