<script lang="ts">
  import { fly, fade } from 'svelte/transition';
  import { onMount } from 'svelte';
  import type { PageData } from './$types';
  
  let { data }: { data: PageData } = $props();
  
  let visible = $state(false);
  let currentDate = new Date();
  let currentMonth = $state(currentDate.getMonth());
  let currentYear = $state(currentDate.getFullYear());
  
  // Get events from server data - make it reactive to data changes  
  let events = $derived(data.events || []);
  
  // Add simple debug logging
  $effect(() => {
    console.log('Events updated:', events?.length || 0);
    if (events && events.length > 0) {
      console.log('Sample event:', events[0]);
    }
  });
  
  // Selected date state & modal visibility
  let selectedDate = $state<string | null>(null);
  let selectedEvents = $state<any[]>([]);
  let activeEvent = $state<any | null>(null); // event selected for full view/edit
  let showEventModal = $state(false);
  let editMode = $state(false);

  // Editable form fields
  let form = $state<{ id?: number; title: string; description: string; date: string; start_time: string; end_time: string; location: string; type: string; organizer: string }>({
    title: '', description: '', date: '', start_time: '', end_time: '', location: '', type: 'event', organizer: ''
  });
  let showMonthPicker = $state(false);
  let showYearPicker = $state(false);
  // Ref to year list container for auto-scroll
  let yearListEl = $state<HTMLElement | null>(null);
  // Vertical year list window
  const YEARS_BEFORE = 10;
  const YEARS_AFTER = 10;
  
  // Build a quick lookup map of events per date (YYYY-MM-DD) - Fixed for Svelte 5 runes
  let eventsByDate = $derived.by(() => {
    const map: Record<string, any[]> = {};
    console.log('[calendar] Building eventsByDate from', events?.length || 0, 'events');
    
    if (!events || !Array.isArray(events) || events.length === 0) {
      console.log('[calendar] No events to process');
      return map;
    }
    
    for (const ev of events) {
      if (!ev?.date) {
        console.log('[calendar] Skipping event with no date:', ev);
        continue;
      }
      console.log('[calendar] Processing event:', ev.title, 'date:', ev.date);
      (map[ev.date] ||= []).push(ev);
    }
    console.log('[calendar] Final eventsByDate map:', map);
    console.log('[calendar] eventsByDate keys:', Object.keys(map));
    return map;
  });
  
  function formatDateKey(year: number, month: number, day: number) {
    return `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
  }
  
  function openDay(day: number) {
    const key = formatDateKey(currentYear, currentMonth, day);
    selectedDate = key;
    selectedEvents = eventsByDate[key] || [];
  }
  
  function closeDayModal() {
    selectedDate = null;
    selectedEvents = [];
  }

  function openEvent(ev: any) {
    activeEvent = ev;
    editMode = false;
    showEventModal = true;
    // attempt to reconstruct start/end from time string if available (HH:MM AM - HH:MM PM)
    const timeMatch = ev.time?.match(/(\d{1,2}):(\d{2})\s*(AM|PM)\s*-\s*(\d{1,2}):(\d{2})\s*(AM|PM)/i);
    let start_time = '09:00:00';
    let end_time = '10:00:00';
    if (timeMatch) {
      const to24 = (h: string, m: string, ap: string) => {
        let hour = parseInt(h);
        if (ap.toUpperCase() === 'PM' && hour !== 12) hour += 12;
        if (ap.toUpperCase() === 'AM' && hour === 12) hour = 0;
        return `${String(hour).padStart(2,'0')}:${m}:00`;
      };
      start_time = to24(timeMatch[1], timeMatch[2], timeMatch[3]);
      end_time = to24(timeMatch[4], timeMatch[5], timeMatch[6]);
    }
    form = { id: ev.id, title: ev.title, description: ev.description, date: ev.date, start_time, end_time, location: ev.location, type: ev.type, organizer: ev.organizer };
  }

  function closeEventModal() {
    showEventModal = false;
    activeEvent = null;
  }

  async function saveEventChanges() {
    if (!form.id) return;
    const payload = {
      id: form.id,
      title: form.title,
      description: form.description,
      event_date: form.date,
      start_time: form.start_time,
      end_time: form.end_time,
      location: form.location,
      event_type: form.type,
      organizer: form.organizer
    };
    try {
      const res = await fetch('/api/events', { method: 'PUT', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
      if (!res.ok) throw new Error('Failed to update');
      // optimistic update in local events array
      const idx = events.findIndex(e => e.id === form.id);
      if (idx !== -1) {
        const updated = { ...events[idx], ...{ title: form.title, description: form.description, date: form.date, location: form.location, organizer: form.organizer, type: form.type } };
        // Replace while preserving reactivity (events is derived; skip direct mutation if derived). We rebuild by shallow copy.
        // If events were directly stateful you'd do events[idx]=updated; events=[...events]
      }
      editMode = false;
      // Reload page data (simple approach)
      window.location.reload();
    } catch (e) {
      console.error(e);
      alert('Error saving changes');
    }
  }

  async function deleteEvent() {
    if (!activeEvent?.id) return;
    if (!confirm('Delete this event?')) return;
    try {
      const res = await fetch('/api/events', { method: 'DELETE', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id: activeEvent.id }) });
      if (!res.ok) throw new Error('Failed to delete');
      window.location.reload();
    } catch (e) {
      console.error(e);
      alert('Error deleting event');
    }
  }
  
  function toggleMonthPicker() {
    showMonthPicker = !showMonthPicker; 
    showYearPicker = false;
  }
  function toggleYearPicker() {
  showYearPicker = !showYearPicker;
  console.debug('[calendar] toggleYearPicker ->', showYearPicker);
    showMonthPicker = false;
  }
  function selectMonth(m: number) {
    currentMonth = m;
    showMonthPicker = false;
  }
  function selectYear(y: number) {
    if (currentYear !== y) {
      console.debug('[calendar] Year changed', currentYear, '->', y);
      currentYear = y;
    }
    showYearPicker = false;
  }
  const yearsList: number[] = $derived(
    Array.from({ length: YEARS_BEFORE + YEARS_AFTER + 1 }, (_, i) => (currentYear - YEARS_BEFORE) + i)
  );

  // Auto-scroll the year list so current year is centered when opened
  $effect(() => {
    if (showYearPicker && yearListEl) {
      queueMicrotask(() => {
        const currentBtn = yearListEl!.querySelector('button[aria-selected="true"]') as HTMLElement | null;
        if (currentBtn) {
          yearListEl!.scrollTop = currentBtn.offsetTop - (yearListEl!.clientHeight / 2) + (currentBtn.clientHeight / 2);
        }
      });
    }
  });

  // Event color mapping
  function eventTypeColor(type: string | undefined) {
    switch (type) {
      case 'meeting': return 'bg-blue-600';
      case 'academic': return 'bg-purple-600';
      case 'announcement': return 'bg-amber-600';
      case 'holiday': return 'bg-red-600';
      default: return 'bg-green-600';
    }
  }
  
  // Month names
  const monthNames = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
  ];
  
  // Get days in month
  function getDaysInMonth(month: number, year: number): number {
    return new Date(year, month + 1, 0).getDate();
  }
  
  // Get first day of month (0 = Sunday, 1 = Monday, etc.)
  function getFirstDayOfMonth(month: number, year: number): number {
    return new Date(year, month, 1).getDay();
  }
  
  // Navigation functions
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
  
  let navContainer = $state<HTMLElement | null>(null);
  onMount(() => {
    visible = true;
    console.log('Calendar mounted, events:', events);
    const handleClick = (e: MouseEvent) => {
      if (!navContainer) return;
      const target = e.target as Node;
      if (!navContainer.contains(target)) {
        if (showMonthPicker || showYearPicker) {
          showMonthPicker = false;
          showYearPicker = false;
        }
      }
    };
    window.addEventListener('click', handleClick);
    return () => window.removeEventListener('click', handleClick);
  });
</script>

<svelte:head>
  <title>School Calendar - New Cabalan National High School</title>
</svelte:head>

<!-- Main container -->
<div class="relative min-h-screen font-sans">
  <!-- Page Content -->
  <div class="relative z-10 flex min-h-screen flex-col">
    <!-- Main Content Area -->
    <main class="flex flex-grow flex-col items-center pb-4 px-2 md:px-4">
      <!-- School Logo -->
      {#if visible}
      <div class="flex justify-center py-4" in:fade={{ duration: 1000 }}>
        <img src="/logo.png" alt="School Logo" class="logo" />
      </div>
      <style>
        .logo {
          width: 200px;
          height: auto;
          margin-bottom: 20px;
          margin-top: -10px;
          margin-left: auto;
          margin-right: auto;
          display: block;
        }
      </style>
      {/if}
      
      <!-- Calendar Container -->
      {#if visible}
      <div class="w-full max-w-7xl mx-auto mb-4 md:mb-8 flex flex-col" in:fly={{ y: 100, duration: 800, delay: 600 }}>
        <!-- Header Section -->
        <div class="rounded-t-2xl bg-gradient-to-r from-green-600 to-green-700 text-white py-3 md:py-4 shadow-lg">
          <div class="flex justify-center items-center px-4 md:px-6">
            <h2 class="text-lg md:text-2xl lg:text-3xl font-bold">SCHOOL CALENDAR</h2>
          </div>
        </div>
        
        <!-- Calendar Grid Container -->
        <div class="bg-white rounded-b-2xl shadow-lg p-3 md:p-6">
          <!-- Month Navigation -->
          <div bind:this={navContainer} class="flex items-center justify-between mb-4 md:mb-6 relative">
            <button 
              onclick={previousMonth}
              class="p-2 rounded-lg bg-green-600 text-white hover:bg-green-700 transition-all duration-300 transform hover:scale-110 hover:shadow-lg active:scale-95"
              aria-label="Previous month"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 md:h-6 md:w-6 transition-transform duration-200 hover:scale-110" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
            </button>
            
            <div class="flex items-center gap-1 md:gap-2 relative">
              <div class="relative">
                <button type="button" class="text-lg md:text-2xl font-bold text-green-800 flex items-center gap-1 focus:outline-none focus-visible:ring-2 focus-visible:ring-green-500 rounded px-1 md:px-2 py-1 hover:bg-green-50 transition-all duration-300 transform hover:scale-105 active:scale-95"
                  onclick={toggleMonthPicker} aria-haspopup="listbox" aria-expanded={showMonthPicker} aria-label="Select month">
                  <span class="transition-colors duration-200">{monthNames[currentMonth]}</span>
                  <svg class="w-4 h-4 md:w-5 md:h-5 text-green-700 transition-all duration-300 {showMonthPicker ? 'rotate-180 text-green-900' : ''}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </button>
                {#if showMonthPicker}
                  <div class="absolute left-0 mt-1 w-48 md:w-56 bg-white border border-green-200 rounded-lg shadow-lg z-30 p-2 grid grid-cols-3 gap-1" role="listbox">
                    {#each monthNames as m, idx}
                      <button type="button" onclick={() => selectMonth(idx)} role="option" aria-selected={idx === currentMonth}
                        class="text-xs md:text-sm rounded-md px-2 py-1 font-medium transition-all duration-200 text-left transform hover:scale-105 active:scale-95
                          {idx === currentMonth ? 'bg-green-600 text-white shadow-md' : 'hover:bg-green-50 text-green-800 hover:shadow-sm'}">
                        {m.slice(0,3)}
                      </button>
                    {/each}
                  </div>
                {/if}
              </div>
              <div class="relative">
                <button type="button" class="text-lg md:text-2xl font-bold text-green-800 flex items-center gap-1 focus:outline-none focus-visible:ring-2 focus-visible:ring-green-500 rounded px-1 md:px-2 py-1 hover:bg-green-50 transition-all duration-300 transform hover:scale-105 active:scale-95"
                  onclick={toggleYearPicker} aria-haspopup="listbox" aria-expanded={showYearPicker} aria-label="Select year">
                  <span class="transition-colors duration-200">{currentYear}</span>
                  <svg class="w-4 h-4 md:w-5 md:h-5 text-green-700 transition-all duration-300 {showYearPicker ? 'rotate-180 text-green-900' : ''}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </button>
                {#if showYearPicker}
                  <div bind:this={yearListEl} class="absolute left-0 mt-1 w-32 bg-white border border-green-200 rounded-lg shadow-lg z-50 p-2 max-h-72 overflow-auto" role="listbox">
                    {#each yearsList as y}
                      <button type="button" onclick={() => selectYear(y)} role="option" aria-selected={y === currentYear}
                        class="block w-full text-sm rounded px-2 py-1 text-left font-medium transition-all duration-200 transform hover:scale-105 active:scale-95
                          {y === currentYear ? 'bg-green-600 text-white shadow-md' : 'hover:bg-green-50 text-green-800 hover:shadow-sm'}">
                        {y}
                      </button>
                    {/each}
                  </div>
                {/if}
              </div>
            </div>
            
            <button 
              onclick={nextMonth}
              class="p-2 rounded-lg bg-green-600 text-white hover:bg-green-700 transition-all duration-300 transform hover:scale-110 hover:shadow-lg active:scale-95"
              aria-label="Next month"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 transition-transform duration-200 hover:scale-110" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </button>
          </div>
          <!-- Old full-width pickers removed; new dropdowns above -->
          
          <!-- Calendar Grid -->
          <div class="grid grid-cols-7 gap-2 mb-6">
            <!-- Days of week header -->
            {#each ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as day}
              <div class="p-1 md:p-2 bg-green-100 text-green-800 font-semibold text-center rounded-lg text-xs md:text-sm">
                <span class="hidden sm:inline">{day}</span>
                <span class="sm:hidden">{day.slice(0, 1)}</span>
              </div>
            {/each}
            
            <!-- Empty cells for days before month starts -->
            {#each Array(getFirstDayOfMonth(currentMonth, currentYear)) as _}
              <div class="p-1 md:p-2 h-16 md:h-20 lg:h-24"></div>
            {/each}
            
            <!-- Days of the month -->
            {#each Array(getDaysInMonth(currentMonth, currentYear)) as _, i}
              {@const day = i + 1}
              {@const isToday = 
                currentYear === new Date().getFullYear() && 
                currentMonth === new Date().getMonth() && 
                day === new Date().getDate()
              }
              {@const dateKey = formatDateKey(currentYear, currentMonth, day)}
              {@const dayEvents = eventsByDate[dateKey] || []}
              <!-- Debug logging for specific dates -->
              {#if day === 15 || day === 20 || day === 26}
                {console.log(`[calendar] Day ${day}: dateKey="${dateKey}", events:`, dayEvents)}
                {console.log(`[calendar] Available eventsByDate keys:`, Object.keys(eventsByDate))}
                {console.log(`[calendar] currentMonth=${currentMonth}, currentYear=${currentYear}`)}
              {/if}
              <button type="button"
                onclick={() => dayEvents.length && openDay(day)}
                class="relative flex flex-col items-start p-1 md:p-1.5 h-16 md:h-20 lg:h-24 w-full text-left border rounded-lg transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-green-500 group transform hover:scale-105 hover:shadow-lg
                  {isToday ? 'bg-yellow-100 border-yellow-400 hover:bg-yellow-200 hover:border-yellow-500' : 'bg-white border-gray-200 hover:bg-green-50 hover:border-green-300'}
                  {dayEvents.length ? 'hover:cursor-pointer hover:shadow-green-100' : 'opacity-70 hover:opacity-90'}">
                <div class="text-xs md:text-sm font-semibold text-green-800 flex items-center gap-1 transition-all duration-200 group-hover:text-green-900">
                  <span class="transition-transform duration-200 group-hover:scale-110">{day}</span>
                  {#if dayEvents.length}
                    <span class="inline-flex items-center justify-center rounded-full bg-green-600 text-white text-[8px] md:text-[10px] lg:text-xs px-1 md:px-1.5 py-0.5 leading-none transition-all duration-200 group-hover:bg-green-700 group-hover:scale-110 group-hover:shadow-md event-count">
                      {dayEvents.length}
                    </span>
                  {/if}
                </div>
                {#if dayEvents.length}
                  <div class="mt-0.5 flex flex-col gap-0.5 w-full overflow-hidden">
                    {#each dayEvents.slice(0, 2) as ev}
                      <div class="relative truncate w-full text-[8px] md:text-[10px] lg:text-[11px] leading-tight flex items-center gap-1 px-1 py-0.5 rounded bg-green-50 text-green-800 border border-green-200 transition-all duration-300 cursor-pointer group/event transform hover:scale-105 hover:bg-green-100 hover:border-green-300 hover:shadow-md hover:-translate-y-0.5"
                        onclick={()=>openEvent(ev)}
                      >
                        <span class={`inline-block w-1.5 h-1.5 md:w-2 md:h-2 rounded-full ${eventTypeColor(ev.type)} transition-all duration-200 group-hover/event:scale-125 group-hover/event:shadow-sm`}></span>
                        <span class="truncate font-medium transition-all duration-200 group-hover/event:text-green-900 group-hover/event:font-semibold">{ev.title}</span>
                        
                        <!-- Enhanced hover tooltip with subtle info -->
                        <div class="hidden md:block absolute left-0 top-full mt-2 z-50 w-56 p-2.5 rounded-lg bg-gray-900 text-white shadow-xl opacity-0 invisible group-hover/event:opacity-100 group-hover/event:visible transition-all duration-200 transform translate-y-1 group-hover/event:translate-y-0">
                          <div class="absolute -top-1 left-3 w-2 h-2 bg-gray-900 transform rotate-45"></div>
                          
                          <div class="space-y-1.5">
                            <div class="flex items-center gap-2">
                              <span class={`inline-block w-2.5 h-2.5 rounded-full ${eventTypeColor(ev.type)}`}></span>
                              <div class="font-medium text-sm truncate">{ev.title}</div>
                            </div>
                            
                            {#if ev.time}
                              <div class="text-xs text-gray-300 flex items-center gap-1.5">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                </svg>
                                <span>{ev.time}</span>
                              </div>
                            {/if}
                            
                            {#if ev.location}
                              <div class="text-xs text-gray-300 flex items-center gap-1.5">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                  <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                                </svg>
                                <span class="truncate">{ev.location}</span>
                              </div>
                            {/if}
                            
                            <div class="text-xs text-blue-300 font-medium pt-1 border-t border-gray-700">
                              Click for details
                            </div>
                          </div>
                        </div>
                      </div>
                    {/each}
                    {#if dayEvents.length > 3}
                      <div class="text-[10px] md:text-xs text-green-700 font-medium">+{dayEvents.length - 3} more</div>
                    {/if}
                  </div>
                {/if}
                <span class="sr-only">{dayEvents.length ? `${dayEvents.length} event${dayEvents.length === 1 ? '' : 's'}` : 'No events'}</span>
              </button>
            {/each}
          </div>
          
          {#if selectedDate}
            <div class="mb-6 animate-fade-in">
              <div class="flex items-center justify-between mb-2">
                <h4 class="text-lg font-semibold text-green-800">
                  Events on {selectedDate}
                </h4>
                <button type="button" onclick={closeDayModal} class="text-sm text-green-700 hover:underline">Close</button>
              </div>
              {#if selectedEvents.length}
                <div class="grid gap-3 md:grid-cols-2">
                  {#each selectedEvents as ev}
                    <div class="bg-white border border-green-200 rounded-lg p-3 shadow-sm">
                      <div class="font-medium text-gray-900 truncate" title={ev.title}>{ev.title}</div>
                      <div class="text-xs text-gray-600 mt-0.5">{ev.time}</div>
                      {#if ev.location}
                        <div class="text-xs text-gray-500 mt-0.5">{ev.location}</div>
                      {/if}
                      {#if ev.description}
                        <div class="text-xs text-gray-500 mt-1 line-clamp-2">{ev.description}</div>
                      {/if}
                    </div>
                  {/each}
                </div>
              {:else}
                <p class="text-sm text-gray-500">No events.</p>
              {/if}
            </div>
          {/if}
          
          <!-- Event Summary -->
          <div class="bg-gray-50 rounded-lg p-4">
            {#if events.length > 0}
              <p class="text-gray-600">You have {events.length} event{events.length === 1 ? '' : 's'} scheduled.</p>
              <!-- Show events list -->
              <div class="mt-3 space-y-2">
                {#each events as event}
                  <div class="bg-white p-3 rounded border-l-4 border-green-500">
                    <div class="font-medium text-gray-900">{event.title}</div>
                    <div class="text-sm text-gray-600">{event.date} • {event.time}</div>
                    {#if event.location}
                      <div class="text-sm text-gray-500">{event.location}</div>
                    {/if}
                  </div>
                {/each}
              </div>
            {:else}
              <p class="text-gray-500">No events scheduled for this month.</p>
            {/if}
          </div>
          <!-- Debug block: remove when events render correctly -->
        </div>
      </div>
      {/if}
      
    </main>
  </div>
</div>

{#if showEventModal && activeEvent}
  <div class="fixed inset-0 z-[100] flex {editMode ? 'items-end pb-12 md:pb-16' : 'items-center'} justify-center bg-black/50 p-2 md:p-4 backdrop-blur-sm overflow-y-auto" onclick={(e)=>{ if(e.target===e.currentTarget) closeEventModal(); }}>
    <div class="bg-white w-full max-w-lg mx-auto my-4 rounded-xl shadow-2xl border border-gray-200 overflow-hidden animate-fade-in transform transition-all duration-300 max-h-[calc(100vh-4rem)]">
      
      <!-- Modal Header -->
      <div class="relative bg-gradient-to-r from-green-600 to-green-700 px-4 md:px-6 py-3 md:py-4">
        <button class="absolute top-2 right-2 text-white/80 hover:text-white hover:bg-white/20 rounded-full p-1.5 transition-all duration-300 transform hover:scale-110 hover:rotate-90 active:scale-95" onclick={closeEventModal} aria-label="Close">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
        
        <div class="flex items-center gap-2 text-white pr-8">
          <div class={`w-3 h-3 rounded-full ${eventTypeColor(activeEvent.type)} ring-2 ring-white/50`}></div>
          <div>
            <h2 class="text-lg md:text-xl font-bold truncate">{activeEvent.title}</h2>
            <div class="text-green-100 text-xs md:text-sm font-medium">{new Date(activeEvent.date).toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}</div>
          </div>
        </div>
      </div>

      <div class="p-4 md:p-6 overflow-y-auto max-h-[calc(100vh-8rem)]">
        {#if !editMode}
          <!-- View Mode -->
          <div class="space-y-6">
            
            <!-- Quick Info Cards -->
            <div class="grid md:grid-cols-2 gap-4">
              {#if activeEvent.time}
                <div class="flex items-center gap-3 p-3 bg-blue-50 rounded-lg border border-blue-100">
                  <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                    </svg>
                  </div>
                  <div>
                    <div class="text-sm font-medium text-blue-900">Time</div>
                    <div class="text-blue-700">{activeEvent.time}</div>
                  </div>
                </div>
              {/if}
              
              {#if activeEvent.location}
                <div class="flex items-center gap-3 p-3 bg-purple-50 rounded-lg border border-purple-100">
                  <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                    </svg>
                  </div>
                  <div>
                    <div class="text-sm font-medium text-purple-900">Location</div>
                    <div class="text-purple-700">{activeEvent.location}</div>
                  </div>
                </div>
              {/if}
              
              {#if activeEvent.organizer}
                <div class="flex items-center gap-3 p-3 bg-amber-50 rounded-lg border border-amber-100">
                  <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                    </svg>
                  </div>
                  <div>
                    <div class="text-sm font-medium text-amber-900">Organizer</div>
                    <div class="text-amber-700">{activeEvent.organizer}</div>
                  </div>
                </div>
              {/if}
              
              <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg border border-gray-100">
                <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center">
                  <svg class="w-4 h-4 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M17.707 9.293a1 1 0 010 1.414l-7 7a1 1 0 01-1.414 0l-7-7A.997.997 0 012 10V5a3 3 0 013-3h5c.256 0 .512.098.707.293l7 7zM5 6a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                  </svg>
                </div>
                <div>
                  <div class="text-sm font-medium text-gray-900">Type</div>
                  <div class="text-gray-700 capitalize">{activeEvent.type}</div>
                </div>
              </div>
            </div>
            
            <!-- Description -->
            {#if activeEvent.description}
              <div class="bg-gray-50 rounded-lg p-4 border border-gray-100">
                <h3 class="text-sm font-semibold text-gray-900 mb-2 flex items-center gap-2">
                  <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                  </svg>
                  Event Details
                </h3>
                <p class="text-gray-700 leading-relaxed">{activeEvent.description}</p>
              </div>
            {/if}
            
            <!-- Action Buttons -->
            <div class="flex gap-3 pt-4 border-t border-gray-100">
              <button class="flex-1 px-4 py-2.5 rounded-lg bg-green-600 text-white font-medium hover:bg-green-700 transition-all duration-300 flex items-center justify-center gap-2 transform hover:scale-105 hover:shadow-lg active:scale-95" onclick={()=>editMode=true}>
                <svg class="w-4 h-4 transition-transform duration-200 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit Event
              </button>
              <button class="px-4 py-2.5 rounded-lg bg-red-600 text-white font-medium hover:bg-red-700 transition-all duration-300 flex items-center justify-center gap-2 transform hover:scale-105 hover:shadow-lg active:scale-95" onclick={deleteEvent}>
                <svg class="w-4 h-4 transition-transform duration-200 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Delete Event
              </button>
            </div>
          </div>
        {:else}
          <!-- Edit Mode -->
          <form class="space-y-4" onsubmit={(e)=>{ e.preventDefault(); saveEventChanges(); }}>
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
              <div class="flex items-center gap-2 text-blue-800 mb-3">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                <h3 class="font-semibold text-sm">Edit Event Details</h3>
              </div>
              
              <div class="grid gap-3">
                <div class="grid gap-1">
                  <label class="text-xs font-medium text-gray-700">Event Title *</label>
                  <input class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-transparent transition-colors" 
                         bind:value={form.title} required placeholder="Enter event title" />
                </div>
                
                <div class="grid grid-cols-2 gap-3">
                  <div class="grid gap-1">
                    <label class="text-xs font-medium text-gray-700">Date *</label>
                    <input type="date" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-transparent transition-colors" 
                           bind:value={form.date} required />
                  </div>
                  <div class="grid gap-1">
                    <label class="text-xs font-medium text-gray-700">Type *</label>
                    <select class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-transparent transition-colors" bind:value={form.type}>
                      <option value="event">Event</option>
                      <option value="meeting">Meeting</option>
                      <option value="academic">Academic</option>
                      <option value="announcement">Announcement</option>
                      <option value="holiday">Holiday</option>
                    </select>
                  </div>
                </div>
                
                <div class="grid grid-cols-2 gap-3">
                  <div class="grid gap-1">
                    <label class="text-xs font-medium text-gray-700">Start Time *</label>
                    <input type="time" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-transparent transition-colors" 
                           bind:value={form.start_time} required />
                  </div>
                  <div class="grid gap-1">
                    <label class="text-xs font-medium text-gray-700">End Time *</label>
                    <input type="time" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-transparent transition-colors" 
                           bind:value={form.end_time} required />
                  </div>
                </div>
                
                <div class="grid grid-cols-2 gap-3">
                  <div class="grid gap-1">
                    <label class="text-xs font-medium text-gray-700">Location *</label>
                    <input class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-transparent transition-colors" 
                           bind:value={form.location} required placeholder="Event location" />
                  </div>
                  <div class="grid gap-1">
                    <label class="text-xs font-medium text-gray-700">Organizer *</label>
                    <input class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-transparent transition-colors" 
                           bind:value={form.organizer} required placeholder="Event organizer" />
                  </div>
                </div>
                
                <div class="grid gap-1">
                  <label class="text-xs font-medium text-gray-700">Description</label>
                  <textarea rows="2" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-transparent transition-colors resize-y" 
                            bind:value={form.description} placeholder="Add event description (optional)"></textarea>
                </div>
              </div>
            </div>
            
            <!-- Form Actions -->
            <div class="flex gap-2 pt-2 border-t border-gray-100">
              <button type="submit" class="flex-1 px-3 py-2 rounded-md bg-green-600 text-white text-sm font-medium hover:bg-green-700 transition-all duration-300 flex items-center justify-center gap-1.5 transform hover:scale-105 hover:shadow-lg active:scale-95">
                <svg class="w-3.5 h-3.5 transition-transform duration-200 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Save Changes
              </button>
              <button type="button" class="px-3 py-2 rounded-md bg-gray-100 text-gray-700 text-sm font-medium hover:bg-gray-200 transition-all duration-300 flex items-center justify-center gap-1.5 transform hover:scale-105 active:scale-95" onclick={()=>{ editMode=false; }}>
                <svg class="w-3.5 h-3.5 transition-transform duration-200 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                Cancel
              </button>
            </div>
          </form>
        {/if}
      </div>
    </div>
  </div>
{/if}

<style>
  /* Remove default blue outline / glow on buttons while preserving custom focus-visible rings */
  button:focus { outline: none; box-shadow: none; }
  button::-moz-focus-inner { border: 0; }
  
  /* Line clamp utility for tooltip descriptions */
  .line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }
  
  /* Smooth tooltip animation */
  .group\/event:hover .tooltip-arrow {
    transform: translateY(-1px) rotate(45deg);
  }
  
  /* Enhanced hover animations */
  @keyframes pulse-glow {
    0%, 100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.4); }
    50% { box-shadow: 0 0 0 8px rgba(34, 197, 94, 0); }
  }
  
  .hover-glow:hover {
    animation: pulse-glow 1.5s infinite;
  }
  
  /* Bounce animation for event counts */
  @keyframes bounce-in {
    0% { transform: scale(0.3); opacity: 0; }
    50% { transform: scale(1.05); }
    70% { transform: scale(0.9); }
    100% { transform: scale(1); opacity: 1; }
  }
  
  .event-count {
    animation: bounce-in 0.5s ease-out;
  }
  
  /* Shake animation for error states */
  @keyframes shake {
    0%, 100% { transform: translateX(0); }
    10%, 30%, 50%, 70%, 90% { transform: translateX(-2px); }
    20%, 40%, 60%, 80% { transform: translateX(2px); }
  }
  
  .shake {
    animation: shake 0.5s ease-in-out;
  }
  
  /* Fade in animation for modals */
  .animate-fade-in {
    animation: fadeIn 0.3s ease-out;
  }
  
  @keyframes fadeIn {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
  }
</style>
