<script lang="ts">
  import PersonNode from "$lib/components/PersonNode.svelte";
  import SimpleAdminLogin from "$lib/components/SimpleAdminLogin.svelte";
  import { fly, fade, slide } from 'svelte/transition';
  import { onMount, onDestroy } from 'svelte';
  import { browser } from '$app/environment';
  import { invalidateAll } from '$app/navigation';
  let { data } = $props();
  interface Faculty {
    id: number;
    name: string;
    role: string;
    department: string;
    email: string | null;
    number: string | null;
    image_url: string | null;
  }

  let faculty = $derived(data.faculty);
  let schoolYearText = $derived(data.schoolYear || "School Year 2024-2025");
  
  let visible = $state(false);
  let searchQuery = $state("");
  let selectedDepartment = $state("All");
  let viewMode = $state("tree"); // "grid" or "tree"
  let currentPage = $state(1); // 1 for full hierarchy, 2 for departmental view
  
  // Admin modal state
  let showAdminModal = $state(false);
  let adminUsername = $state("");
  let adminPassword = $state("");
  let loginError = $state("");
  let isAdminLoggedIn = $state(data.loggedIn);
  
  // Debug admin status
  // $: console.log('Faculty page - isAdminLoggedIn:', isAdminLoggedIn);
  
  // Faculty editing state
  let showEditModal = $state(false);
  let editingFaculty: Faculty | null = $state(null);
  let isCreatingNew = $state(false);
  let editForm = $state({
    name: "",
    role: "",
    department: "",
    email: "",
    number: "",
    image_url: ""
  });

  let isUpdating = $state(false);
  let uploadingImage = $state(false);
  let imageFile: File | null = $state(null);
  
  // Cookie checking for admin status
  let cookieCheckInterval: NodeJS.Timeout | null = null;
  
  // School year text state
  let showSchoolYearEdit = $state(false);
  let editingSchoolYear = $state("");
  
  // Window width for responsive design
  let windowWidth = $state(1024); // Default to desktop width
  
  // Departments array
  const departments = [
    "All",
    "Administration",
    "Mathematics",
    "Science",
    "English",
    "Filipino",
    "Social Studies",
    "MAPEH",
    "TLE",
    "Maintenance"
  ];
  
  // Centralized department color legend
  const deptColors: Record<string, string> = {
    Administration: "#FFFF00", // Yellow
    Mathematics: "#3B82F6",    // Blue
    Science: "#008000",        // Green
    English: "#FF0000",        // Red
    Filipino: "#964B00",       // Brown
    "Social Studies": "#10B981", // Teal/Green
    MAPEH: "#B200ED",          // Purple
    TLE: "#000000",            // Black
    Maintenance: "#FF6600"      // Orange
  };
  
  function colorFor(dept?: string) {
    if (!dept) return "#6B7280"; // gray fallback
    return deptColors[dept] || "#6B7280";
  }
  
  // Helper function to get the principal
  function getPrincipal() {
    return filteredFaculty.find((f: Faculty) => f.role === "Principal");
  }
  
  // Helper function to get the assistant principal
  function getAssistantPrincipal() {
    return filteredFaculty.find((f: Faculty) => f.role === "Assistant Principal");
  }
  
  // Helper function to get master teachers
  function getMasterTeachers() {
    return filteredFaculty.filter((f: Faculty) => f.role === "Master Teacher");
  }
  
  // Helper function to get regular teachers by department
  function getTeachersByDepartment(department: string) {
    return filteredFaculty.filter((f: Faculty) =>
      f.department === department &&
      f.role !== "Master Teacher" &&
      f.role !== "Principal" &&
      f.role !== "Assistant Principal"
    );
  }
  
  // Filter faculty based on search and department selection
  let filteredFaculty = $derived(faculty.filter((f: Faculty) => {
    const matchesSearch = f.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
                        f.role.toLowerCase().includes(searchQuery.toLowerCase()) ||
                        f.department.toLowerCase().includes(searchQuery.toLowerCase());
    const matchesDepartment = selectedDepartment === "All" || f.department === selectedDepartment;
    return matchesSearch && matchesDepartment;
  }));
  
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
  
  // Faculty management functions
  function openEditModal(faculty?: Faculty) {
    if (faculty) {
      editingFaculty = faculty;
      editForm = {
        name: faculty.name,
        role: faculty.role,
        department: faculty.department,
        email: faculty.email || "",
        number: faculty.number || "",
        image_url: faculty.image_url || ""
      };
      isCreatingNew = false;
    } else {
      editingFaculty = null;
      editForm = {
        name: "",
        role: "",
        department: "",
        email: "",
        number: "",
        image_url: ""
      };
      isCreatingNew = true;
    }
    showEditModal = true;
  }
  
  function closeEditModal() {
    showEditModal = false;
    editingFaculty = null;
    isCreatingNew = false;
    imageFile = null;
    uploadingImage = false;
  }
  
  async function handleImageUpload(event: Event) {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];
    
    if (!file) return;
    
    // Validate file size (5MB limit)
    if (file.size > 5 * 1024 * 1024) {
      alert('Image file size must be less than 5MB');
      return;
    }
    
    // Validate file type
    if (!file.type.startsWith('image/')) {
      alert('Please select a valid image file');
      return;
    }
    
    uploadingImage = true;
    imageFile = file;
    
    try {
      const formData = new FormData();
      formData.append('image', file);
      
  const response = await fetch('http://localhost/upload_faculty_image.php', {
        method: 'POST',
        body: formData
      });
      
      const result = await response.json();
      
      if (result.success) {
        editForm.image_url = result.image_url;
        showSuccessNotification('Image uploaded successfully!');
      } else {
        alert('Error uploading image: ' + (result.error || 'Unknown error'));
      }
    } catch (error) {
      console.error('Error uploading image:', error);
      alert('Error uploading image. Please try again.');
    } finally {
      uploadingImage = false;
    }
  }
  
  async function saveFaculty() {
    if (!editForm.name || !editForm.role || !editForm.department) {
      alert('Please fill in all required fields');
      return;
    }

    isUpdating = true;
    
    try {
  const url = 'http://localhost/manage_faculty.php';
      const method = isCreatingNew ? 'POST' : 'PUT';
      const body = isCreatingNew ? editForm : { ...editForm, id: editingFaculty?.id };
      
      console.log('Saving faculty with method:', method);
      console.log('Request body:', JSON.stringify(body, null, 2));
      console.log('URL:', url);
      
      const response = await fetch(url, {
        method,
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(body)
      });
      
      console.log('Response status:', response.status);
      console.log('Response headers:', response.headers);
      
      const responseText = await response.text();
      console.log('Raw response text:', responseText);
      
      let result;
      try {
        result = JSON.parse(responseText);
        console.log('Response result:', result);
      } catch (parseError) {
        console.error('Error parsing JSON response:', parseError);
        console.log('Response was not valid JSON:', responseText);
        throw new Error('Invalid JSON response from server: ' + responseText);
      }
      
      if (result.success) {
        closeEditModal();
        showSuccessNotification(result.message);
        // Refresh the page to show updated data
        invalidateAll();
      } else {
        alert('Error: ' + (result.error || 'Unknown error occurred'));
      }
    } catch (error) {
      console.error('Error saving faculty:', error);
      console.error('Error details:', error.message);
      alert('Error saving faculty member. Please try again.');
    } finally {
      isUpdating = false;
    }
  }
  
  async function deleteFaculty(id: number) {
    if (confirm('Are you sure you want to delete this faculty member?')) {
      try {
  const response = await fetch('http://localhost/manage_faculty.php', {
          method: 'DELETE',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({ id })
        });
        
        const result = await response.json();
        
        if (result.success) {
          // Instantly reload the page
          location.reload();
        } else {
          alert('Error: ' + (result.error || 'Unknown error occurred'));
        }
      } catch (error) {
        console.error('Error deleting faculty:', error);
        alert('Error deleting faculty member. Please try again.');
      }
    }
  }
  
  async function handleAdminLogout() {
    if (browser) {
      document.cookie = 'auth=; path=/; expires=Thu, 01 Jan 1970 00:00:01 GMT;';
      isAdminLoggedIn = false;
      showSuccessNotification('Logged out successfully!');
    }
  }

  // PersonNode event handlers
  function handleEditFaculty(event: CustomEvent) {
    const facultyData = event.detail;
    console.log('Edit faculty event:', facultyData);
    // Convert the faculty data to match the Faculty interface
    const faculty = {
      id: typeof facultyData.id === 'string' ? parseInt(facultyData.id) : facultyData.id,
      name: facultyData.name,
      role: facultyData.role,
      department: facultyData.department,
      email: facultyData.email,
      number: facultyData.number,
      image_url: facultyData.imageUrl || facultyData.image_url
    };
    openEditModal(faculty);
  }

  function handleDeleteFaculty(event: CustomEvent) {
    const facultyData = event.detail;
    console.log('Delete faculty event:', facultyData);
    if (confirm(`Are you sure you want to delete ${facultyData.name}?`)) {
      // Convert id to number if it's a string
      const facultyId = typeof facultyData.id === 'string' ? parseInt(facultyData.id) : facultyData.id;
      deleteFaculty(facultyId);
    }
  }

  // School year editing functions
  function startEditingSchoolYear() {
    editingSchoolYear = schoolYearText;
    showSchoolYearEdit = true;
  }
  
  function saveSchoolYear() {
    if (editingSchoolYear.trim()) {
      // Implementation would save to backend
      showSchoolYearEdit = false;
      showSuccessNotification('School year updated successfully!');
    }
  }
  
  function cancelSchoolYearEdit() {
    showSchoolYearEdit = false;
    editingSchoolYear = "";
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
  
  // Notification functions
  let notificationMessage = $state('');
  let notificationType: 'success' | 'error' = $state('success');
  let showNotification = $state(false);
  
  // Mobile profile popup state
  let showMobileProfileModal = $state(false);
  let selectedProfile: {
    name: string;
    role: string;
    email: string;
    number: string;
    departmentColor: string;
  } | null = $state(null);
  
  function handleProfileClick(event: CustomEvent) {
    // Only show modal on mobile devices
    if (windowWidth < 768) {
      selectedProfile = event.detail;
      showMobileProfileModal = true;
    }
  }
  
  function closeMobileProfileModal() {
    showMobileProfileModal = false;
    selectedProfile = null;
  }
  
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
  
  onMount(() => {
    visible = true;
    checkAdminStatus();
    startCookieCheck();
  });
  
  onDestroy(() => {
    stopCookieCheck();
  });
  
</script>

<svelte:window bind:innerWidth={windowWidth} />

<!-- Main container -->
<div class="relative min-h-screen font-sans">

  <!-- Page Content (positioned above overlay) -->
  <div class="relative z-10 flex min-h-screen flex-col">
    
    <!-- Main Content Area -->
    <main class="flex flex-grow flex-col items-center">
      <!-- School Logo -->
      {#if visible}
<div class="flex flex-col items-center justify-center text-center gap-2 mt-4 md:mt-6 mb-4" in:fade={{ duration: 1000 }}>
  <!-- Logo -->
  <div class="flex justify-center">
  <img src="/logo.png" alt="School Logo" class="logo" />
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
  </div>
</div>
{/if}
      
      <!-- Modern Faculty Container -->
      {#if visible}
      <div class="w-full max-w-6xl mx-auto mb-8 flex flex-col px-4 sm:px-6 lg:px-8" in:fly={{ y: 100, duration: 800, delay: 600 }}>
        <!-- Header Section -->
        <div class="rounded-t-2xl bg-gradient-to-r from-green-600 to-green-700 text-white py-3 md:py-4 shadow-lg">
          <div class="flex justify-between items-center px-3 md:px-6">
            <!-- Left side: Empty spacer -->
            <div class="flex-1"></div>
            
            <!-- Center: Title -->
            <div class="flex items-center gap-2">
              {#if showSchoolYearEdit && isAdminLoggedIn}
                <input
                  type="text"
                  bind:value={editingSchoolYear}
                  class="text-lg md:text-xl lg:text-2xl xl:text-3xl font-bold bg-white/20 text-white placeholder-white/70 border border-white/30 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-white/50"
                  placeholder="Enter school year"
                  on:keydown={(e) => {
                    if (e.key === 'Enter') saveSchoolYear();
                    if (e.key === 'Escape') cancelSchoolYearEdit();
                  }}
                  on:blur={saveSchoolYear}
                />
                <button
                  on:click={saveSchoolYear}
                  class="text-white hover:text-green-200 transition-colors"
                  title="Save"
                  aria-label="Save school year"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                </button>
                <button
                  on:click={cancelSchoolYearEdit}
                  class="text-white hover:text-red-200 transition-colors"
                  title="Cancel"
                  aria-label="Cancel school year edit"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              {:else}
                <h2 class="text-lg md:text-xl lg:text-2xl xl:text-3xl font-bold pl-3 md:pl-3 pr-2 md:pr-4">{schoolYearText.toUpperCase()}</h2>
                {#if isAdminLoggedIn}
                  <button
                    on:click={startEditingSchoolYear}
                    class="text-white hover:text-yellow-200 transition-colors ml-1 md:ml-2"
                    title="Edit school year"
                    aria-label="Edit school year"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </button>
                {/if}
              {/if}
            </div>
            
            <!-- Right side: Admin controls -->
            <div class="flex-1 flex justify-end ml-2 sm:ml-4">
              {#if isAdminLoggedIn}
                <div class="flex items-center gap-1 md:gap-2">
                  {#if isUpdating}
                    <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin" title="Syncing data..."></div>
                  {/if}
                  <button
                    on:click={() => openEditModal()}
                    class="bg-white/20 hover:bg-white/30 text-white px-2 py-1 md:px-3 md:py-1 rounded-lg text-xs md:text-sm font-medium transition-colors flex items-center gap-1"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 md:h-4 md:w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    <span class="hidden sm:inline">Add Faculty</span>
                    <span class="sm:hidden">Add</span>
                  </button>
                  <button 
                    on:click={handleAdminLogout}
                    class="bg-red-600 hover:bg-red-700 text-white px-2 py-1 md:px-3 md:py-1 rounded-lg text-xs md:text-sm font-medium transition-colors"
                  >
                    Logout
                  </button>
                </div>
              {/if}
            </div>
          </div>
        </div>
        
        <!-- Main Content Box with Gradient Border -->
        <div class="bg-gradient-to-r from-green-600 to-green-700 px-2 md:px-3 pt-2 md:pt-3 pb-3 md:pb-5 shadow-2xl min-h-[70vh] rounded-b-xl">
          <div class="bg-white p-3 md:p-4 lg:p-6 rounded-xl h-full flex flex-col">
            
            <!-- Faculty Header with Page Indicator -->
            <div class="flex justify-between items-center mb-4 md:mb-6 flex-shrink-0">
              <div class="w-8 md:w-16"></div> <!-- Spacer for balance -->
              <h3 class="text-lg md:text-xl lg:text-2xl font-semibold text-green-800 text-center">School Faculty</h3>
              <div class="text-xs md:text-sm text-green-600 font-medium">
                Page {currentPage} of 2
              </div>
            </div>
            
            <!-- Scrollable Content Area -->
            <div class="flex-1 overflow-y-auto pr-1 md:pr-2">
        
        <!-- Search and Filter Controls -->
        <div class="mb-2 md:mb-3 flex flex-col md:flex-row gap-2 md:gap-3 justify-between items-center bg-gray-100 rounded-2xl p-3 md:p-4 shadow-md">
          <!-- Search Box -->
          <div class="relative w-full md:w-1/4">
            <div class="flex items-center border border-green-300 rounded-lg bg-white focus-within:ring-1 focus-within:ring-green-500 focus-within:border-green-500 overflow-hidden">
              <div class="pl-2 md:pl-3 pointer-events-none">
                <svg class="w-4 h-4 text-green-800" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                  <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                </svg>
              </div>
              <input
                type="search"
                bind:value={searchQuery}
                class="block w-full p-2 md:p-3 text-sm text-green-900 bg-transparent border-none focus:ring-0"
                placeholder="Search for faculty members..."
              />
            </div>
          </div>
          
          <div class="flex gap-2 md:gap-3 w-full md:w-auto">
            <!-- Department Filter -->
            <div class="flex-1 md:flex-none">
              <select 
                bind:value={selectedDepartment}
                class="w-full md:w-auto bg-white border border-green-300 text-green-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 p-2 md:p-3"
              >
                {#each departments as department}
                  <option value={department}>{department}</option>
                {/each}
              </select>
            </div>
            
            <!-- View Toggle (Hide on mobile) -->
            {#if windowWidth >= 768}
            <div class="flex border border-green-300 rounded-lg overflow-hidden">
              <button 
                class={`h-full p-2 md:p-3 text-sm font-medium ${viewMode === 'tree' ? 'bg-green-600 text-white' : 'bg-white text-green-900 hover:bg-green-100'}`}
                on:click={() => viewMode = 'tree'}
                aria-label="Tree view"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12s-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-8.684l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.368a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z" />
                </svg>
              </button>
              <button 
                class={`h-full p-2 md:p-3 text-sm font-medium ${viewMode === 'grid' ? 'bg-green-600 text-white' : 'bg-white text-green-900 hover:bg-green-100'}`}
                on:click={() => viewMode = 'grid'}
                aria-label="Grid view"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6zM14 6a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2a2 2 0 0 1-2 2h-2a2 2 0 0 1-2-2V6zM4 16a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-2zM14 16a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2a2 2 0 0 1-2 2h-2a2 2 0 0 1-2-2v-2z" />
                </svg>
              </button>
            </div>
            {/if}
          </div>
        </div>
        
        <!-- Dynamic Faculty View -->
        {#if viewMode === 'grid' || windowWidth < 768}
          <!-- Grid View -->
          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6 lg:gap-8 pb-20">
            {#each filteredFaculty as faculty (faculty.id)}
              <div
                class="flex justify-center relative group"
                in:fly={{y: 20, duration: 300, delay: 100 + (filteredFaculty.indexOf(faculty) * 50)}}
              >
                <div class="relative">
                  <PersonNode 
                    name={faculty.name} 
                    role={faculty.department !== "Administration" ? `${faculty.role} - ${faculty.department}` : faculty.role} 
                    scale={0.9}
                    email={faculty.email || ""}
                    number={faculty.number || ""}
                    imageUrl={faculty.image_url || ""}
                    departmentColor={colorFor(faculty.department)}
                    id={faculty.id}
                    department={faculty.department}
                    isLoggedIn={isAdminLoggedIn}
                    on:profileClick={handleProfileClick}
                    on:editFaculty={handleEditFaculty}
                    on:deleteFaculty={handleDeleteFaculty}
                  />
                  {#if isAdminLoggedIn}
                    <div class="absolute -top-2 -right-2 opacity-0 group-hover:opacity-100 transition-all duration-300 transform group-hover:scale-105 flex gap-1">
                      <button 
                        on:click={() => openEditModal(faculty)}
                        class="bg-blue-500 hover:bg-blue-600 text-white p-1 rounded-full shadow-lg transform hover:scale-110 transition-all duration-200"
                        title="Edit Faculty"
                        aria-label="Edit Faculty"
                      >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                      </button>
                      <button 
                        on:click={() => deleteFaculty(faculty.id)}
                        class="bg-red-500 hover:bg-red-600 text-white p-1 rounded-full shadow-lg transform hover:scale-110 transition-all duration-200"
                        title="Delete Faculty"
                        aria-label="Delete Faculty"
                      >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                      </button>
                    </div>
                  {/if}
                </div>
              </div>
            {/each}
            
            {#if filteredFaculty.length === 0}
              <div class="col-span-full text-center py-8 md:py-12">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 md:h-16 md:w-16 mx-auto text-green-600/50" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none">
                  <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                  <path d="M8 8a3.5 3 0 0 1 3.5 -3h1a3.5 3 0 0 1 3.5 3a3 3 0 0 1 -2 3a3 4 0 0 0 -2 4" />
                  <path d="M12 19l0 .01" />
                </svg>
                <p class="mt-4 text-green-800 text-base md:text-lg">No faculty members found matching your criteria.</p>
                <button 
                  class="mt-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
                  on:click={() => { searchQuery = ""; selectedDepartment = "All"; }}
                >
                  Reset Filters
                </button>
              </div>
            {/if}
          </div>
        {:else}
          <!-- Hierarchical View (Tree View) -->
          <div class="w-full space-y-8 md:space-y-12">
            {#if currentPage === 1}
              <!-- Leadership Section -->
              {#if !searchQuery || (getPrincipal() || getAssistantPrincipal())}
                {#if getPrincipal()}
                  <div class="mt-24 space-y-8 flex flex-col items-center" in:fly={{ y: 30, duration: 600, delay: 300 }}>
                    <div class="text-center pt-4">
                      <h4 class="text-base md:text-lg font-medium text-green-700 mb-2">School Principal</h4>
                      <div class="flex justify-center"><div class="h-1 w-20 rounded-full" style="background-color: {colorFor('Administration')}"></div></div>
                    </div>
                    <!-- Separator line -->
                    <div class="w-full flex justify-center">
                      <div class="h-0.5 w-48 rounded-full" style="background-color: {colorFor('Administration')}20;"></div>
                    </div>
                    <div class="flex justify-center">
                      <div class="relative group">
                        <PersonNode name={getPrincipal()?.name || ''} role={getPrincipal()?.role || ''} email={getPrincipal()?.email || ''} number={getPrincipal()?.number || ''} imageUrl={getPrincipal()?.image_url || ''} departmentColor={colorFor('Administration')} id={getPrincipal()?.id || 0} department="Administration" scale={1.3} isLoggedIn={isAdminLoggedIn} on:profileClick={handleProfileClick} on:editFaculty={handleEditFaculty} on:deleteFaculty={handleDeleteFaculty} />
                        {#if isAdminLoggedIn && getPrincipal()}
                          <div class="absolute -top-6 -right-2 opacity-0 group-hover:opacity-100 transition-all duration-300 transform group-hover:scale-105 flex gap-1">
                            <button on:click={() => openEditModal(getPrincipal())} class="bg-blue-500 hover:bg-blue-600 text-white p-1 rounded-full shadow-lg transform hover:scale-110 transition-all duration-200" title="Edit Faculty" aria-label="Edit Principal">
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            </button>
                            <button on:click={() => deleteFaculty(getPrincipal()?.id || 0)} class="bg-red-500 hover:bg-red-600 text-white p-1 rounded-full shadow-lg transform hover:scale-110 transition-all duration-200" title="Delete Faculty" aria-label="Delete Principal">
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            </button>
                          </div>
                        {/if}
                      </div>
                    </div>
                  </div>
                {/if}
                {#if getAssistantPrincipal()}
                  <div class="space-y-6" in:fly={{ y: 30, duration: 600, delay: 400 }}>
                    <!-- Department Header -->
                    <div class="text-center">
                      <h4 class="text-base md:text-lg font-medium text-green-700 mb-2">Administration</h4>
                      <div class="flex justify-center">
                        <div class="h-1 w-20 rounded-full" style="background-color: {colorFor('Administration')}"></div>
                      </div>
                    </div>
                    
                    <!-- Assistant Principal -->
                    <div class="flex justify-center">
                      <div class="relative group">
                        <PersonNode 
                          name={getAssistantPrincipal()?.name || ''} 
                          role={getAssistantPrincipal()?.role || ''} 
                          email={getAssistantPrincipal()?.email || ''} 
                          number={getAssistantPrincipal()?.number || ''} 
                          imageUrl={getAssistantPrincipal()?.image_url || ''} 
                          departmentColor={colorFor('Administration')} 
                          id={getAssistantPrincipal()?.id || 0} 
                          department="Administration" 
                          scale={1.0} 
                          isLoggedIn={isAdminLoggedIn} 
                          on:profileClick={handleProfileClick} 
                          on:editFaculty={handleEditFaculty} 
                          on:deleteFaculty={handleDeleteFaculty} 
                        />
                        {#if isAdminLoggedIn && getAssistantPrincipal()}
                          <div class="absolute -top-2 -right-2 opacity-0 group-hover:opacity-100 transition-all duration-300 transform group-hover:scale-105 flex gap-1">
                            <button 
                              on:click={() => openEditModal(getAssistantPrincipal())}
                              class="bg-blue-500 hover:bg-blue-600 text-white p-1 rounded-full shadow-lg transform hover:scale-110 transition-all duration-200"
                              title="Edit Faculty"
                              aria-label="Edit Assistant Principal"
                            >
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                              </svg>
                            </button>
                            <button 
                              on:click={() => deleteFaculty(getAssistantPrincipal()?.id || 0)}
                              class="bg-red-500 hover:bg-red-600 text-white p-1 rounded-full shadow-lg transform hover:scale-110 transition-all duration-200"
                              title="Delete Faculty"
                              aria-label="Delete Assistant Principal"
                            >
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                              </svg>
                            </button>
                          </div>
                        {/if}
                      </div>
                    </div>
                  </div>
                {/if}
              {/if}
              {#if getMasterTeachers().length > 0}
                <div class="space-y-12">
                  <!-- Only show the header if we're not searching or if search includes department faculty -->
                  {#if !searchQuery || getMasterTeachers().some(m => getTeachersByDepartment(m.department).length > 0)}
                    <h3 class="text-center text-lg md:text-xl font-semibold text-green-800">Departments and Faculty</h3>
                  {/if}
                  
                  {#each getMasterTeachers() as master, deptIndex}
                    {@const departmentTeachers = getTeachersByDepartment(master.department)}
                    <!-- Only show department if master teacher OR department teachers exist in filtered results -->
                    {#if filteredFaculty.includes(master) || departmentTeachers.length > 0}
                      <div class="space-y-6" in:fly={{ y: 30, duration: 600, delay: 500 + (deptIndex * 200) }}>
                        <!-- Department Header -->
                        <div class="text-center">
                          <h4 class="text-base md:text-lg font-medium text-green-700 mb-2">{master.department} Department</h4>
                          <div class="flex justify-center">
                            <div class="h-1 w-20 rounded-full" 
                                 style={`background-color: ${colorFor(master.department)}`}></div>
                          </div>
                        </div>
                        
                        <!-- Master Teacher (Department Head) - only show if in filtered results -->
                        {#if filteredFaculty.includes(master)}
                          <div class="flex justify-center">
                            <div class="relative group">
                              <PersonNode 
                                name={master.name} 
                                role={`Master Teacher - ${master.department}`} 
                                email={master.email || ""}
                                number={master.number || ""}
                                imageUrl={master.image_url || ""}
                                departmentColor={colorFor(master.department)}
                                id={master.id}
                                department={master.department}
                                scale={1.0}
                                isLoggedIn={isAdminLoggedIn}
                                on:profileClick={handleProfileClick}
                                on:editFaculty={handleEditFaculty}
                                on:deleteFaculty={handleDeleteFaculty}
                              />
                              {#if isAdminLoggedIn}
                                <div class="absolute -top-2 -right-2 opacity-0 group-hover:opacity-100 transition-all duration-300 transform group-hover:scale-105 flex gap-1">
                                  <button 
                                    on:click={() => openEditModal(master)}
                                    class="bg-blue-500 hover:bg-blue-600 text-white p-1 rounded-full shadow-lg transform hover:scale-110 transition-all duration-200"
                                    title="Edit Faculty"
                                    aria-label="Edit Master Teacher"
                                  >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                  </button>
                                  <button 
                                    on:click={() => deleteFaculty(master.id)}
                                    class="bg-red-500 hover:bg-red-600 text-white p-1 rounded-full shadow-lg transform hover:scale-110 transition-all duration-200"
                                    title="Delete Faculty"
                                    aria-label="Delete Master Teacher"
                                  >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                  </button>
                                </div>
                              {/if}
                            </div>
                          </div>
                        {/if}
                        
                        <!-- Department Teachers under the Master Teacher -->
                        {#if departmentTeachers.length > 0}
                          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 md:gap-6 ml-8 md:ml-12">
                            {#each departmentTeachers as teacher, j}
                              <div class="flex justify-center" in:fly={{ y: 20, duration: 400, delay: 700 + (deptIndex * 200) + (j * 50) }}>
                                <div class="relative group">
                                  <PersonNode 
                                    name={teacher.name} 
                                    role={teacher.role} 
                                    email={teacher.email || ""}
                                    number={teacher.number || ""}
                                    imageUrl={teacher.image_url || ""}
                                    scale={0.85}
                                    departmentColor={colorFor(teacher.department)}
                                    id={teacher.id}
                                    department={teacher.department}
                                    isLoggedIn={isAdminLoggedIn}
                                    on:profileClick={handleProfileClick}
                                    on:editFaculty={handleEditFaculty}
                                    on:deleteFaculty={handleDeleteFaculty}
                                  />
                                  {#if isAdminLoggedIn}
                                    <div class="absolute -top-2 -right-2 opacity-0 group-hover:opacity-100 transition-all duration-300 transform group-hover:scale-105 flex gap-1">
                                      <button 
                                        on:click={() => openEditModal(teacher)}
                                        class="bg-blue-500 hover:bg-blue-600 text-white p-1 rounded-full shadow-lg transform hover:scale-110 transition-all duration-200"
                                        title="Edit Faculty"
                                        aria-label="Edit Teacher"
                                      >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                      </button>
                                      <button 
                                        on:click={() => deleteFaculty(teacher.id)}
                                        class="bg-red-500 hover:bg-red-600 text-white p-1 rounded-full shadow-lg transform hover:scale-110 transition-all duration-200"
                                        title="Delete Faculty"
                                        aria-label="Delete Teacher"
                                      >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                      </button>
                                    </div>
                                  {/if}
                                </div>
                              </div>
                            {/each}
                          </div>
                        {/if}
                      </div>
                    {/if}
                  {/each}
                </div>
              {/if}
              
              <!-- No results found for hierarchical view -->
              {#if filteredFaculty.length === 0}
                <div class="text-center py-8 md:py-12">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 md:h-16 md:w-16 mx-auto text-green-600/50" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                    <path d="M8 8a3.5 3 0 0 1 3.5 -3h1a3.5 3 0 0 1 3.5 3a3 3 0 0 1 -2 3a3 4 0 0 0 -2 4" />
                    <path d="M12 19l0 .01" />
                  </svg>
                  <p class="mt-4 text-green-800 text-base md:text-lg">No faculty members found matching your criteria.</p>
                  <button 
                    class="mt-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
                    on:click={() => {
                      searchQuery = "";
                      selectedDepartment = "All";
                    }}
                  >
                    Reset Filters
                  </button>
                </div>
              {/if}
            {:else}
              <!-- Page 2: Teachers by Department (Legend Order) -->
              <div class="space-y-8 md:space-y-12">
                <h1 class="text-center text-lg md:text-3xl font-semibold text-green-800">Department Faculty</h1>
                {#each departments.filter(d => d !== 'All') as dept, deptIndex}
                  {@const teachers = filteredFaculty.filter((f) => f.department === dept && f.role !== 'Principal' && f.role !== 'Assistant Principal')}
                  <!-- Only show department if it has teachers in filtered results -->
                  {#if teachers.length > 0}
                    <div class="space-y-4" in:fly={{ y: 20, duration: 500, delay: 800 + (deptIndex * 200) }}>
                      <!-- Department Header with matching legend color -->
                      <div class="text-center">
                        <h4 class="text-base md:text-lg font-medium text-green-700 mb-1">{dept} Department</h4>
                        <div class="flex justify-center">
                          <div class="h-1 w-20 rounded-full" 
                               style={`background-color: ${colorFor(dept)}`}></div>
                        </div>
                      </div>
                      
                      <!-- Department Teachers -->
                      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 md:gap-6">
                        {#each teachers as teacher, j}
                          <div class="flex justify-center" in:fly={{ y: 20, duration: 400, delay: 900 + (deptIndex * 200) + (j * 50) }}>
                            <div class="relative group">
                              <PersonNode 
                                name={teacher.name} 
                                role={teacher.role} 
                                email={teacher.email || ""}
                                number={teacher.number || ""}
                                imageUrl={teacher.image_url || ""}
                                scale={0.85}
                                departmentColor={colorFor(teacher.department)}
                                id={teacher.id}
                                department={teacher.department}
                                isLoggedIn={isAdminLoggedIn}
                                on:profileClick={handleProfileClick}
                                on:editFaculty={handleEditFaculty}
                                on:deleteFaculty={handleDeleteFaculty}
                              />
                              {#if isAdminLoggedIn}
                                <div class="absolute -top-2 -right-2 opacity-0 group-hover:opacity-100 transition-all duration-300 transform group-hover:scale-105 flex gap-1">
                                  <button 
                                    on:click={() => openEditModal(teacher)}
                                    class="bg-blue-500 hover:bg-blue-600 text-white p-1 rounded-full shadow-lg transform hover:scale-110 transition-all duration-200"
                                    title="Edit Faculty"
                                    aria-label="Edit Teacher"
                                  >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                  </button>
                                  <button 
                                    on:click={() => deleteFaculty(teacher.id)}
                                    class="bg-red-500 hover:bg-red-600 text-white p-1 rounded-full shadow-lg transform hover:scale-110 transition-all duration-200"
                                    title="Delete Faculty"
                                    aria-label="Delete Teacher"
                                  >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                  </button>
                                </div>
                              {/if}
                            </div>
                          </div>
                        {/each}
                      </div>
                    </div>
                  {/if}
                {/each}
                
                <!-- No results found for Page 2 -->
                {#if filteredFaculty.length === 0}
                  <div class="text-center py-8 md:py-12">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 md:h-16 md:w-16 mx-auto text-green-600/50" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none">
                      <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                      <path d="M8 8a3.5 3 0 0 1 3.5 -3h1a3.5 3 0 0 1 3.5 3a3 3 0 0 1 -2 3a3 4 0 0 0 -2 4" />
                      <path d="M12 19l0 .01" />
                    </svg>
                    <p class="mt-4 text-green-800 text-base md:text-lg">No faculty members found matching your criteria.</p>
                    <button 
                      class="mt-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
                      on:click={() => { searchQuery = ""; selectedDepartment = "All"; }}
                    >
                      Reset Filters
                    </button>
                  </div>
                {/if}
              </div>
            {/if}
          </div>
        {/if}
        <div class="mt-8 md:mt-12 pt-4 md:pt-6 border-t border-green-600/30 bg-gray-100 rounded-xl p-4">
          <h3 class="text-center text-base md:text-lg font-semibold text-green-800 mb-3 md:mb-4">Department Color Legend</h3>
          <div class="flex flex-wrap justify-center gap-2 md:gap-3">
            {#each departments.filter(d => d !== "All") as department}
              <div class="flex items-center gap-2 px-2 md:px-3 py-1 bg-white rounded-full border border-green-300">
       <div class="w-3 h-3 rounded-full" 
         style={`background-color: ${colorFor(department)}`}></div>
                <span class="text-xs font-medium text-green-900">{department}</span>
              </div>
            {/each}
          </div>
        </div>
        
        <!-- Navigation Buttons -->
        <div class="flex justify-center mt-4 md:mt-6 gap-2 md:gap-4">
          <button 
            class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-2 md:px-6 md:py-3 rounded-full shadow-lg transition-colors flex items-center gap-1 md:gap-2 text-sm md:text-base"
            on:click={() => currentPage = 1}
            disabled={currentPage === 1}
            class:opacity-50={currentPage === 1}
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 md:h-5 md:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            <span class="hidden sm:inline">Leadership</span>
            <span class="sm:hidden">Lead</span>
          </button>
          <button 
            class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-2 md:px-6 md:py-3 rounded-full shadow-lg transition-colors flex items-center gap-1 md:gap-2 text-sm md:text-base"
            on:click={() => currentPage = 2}
            disabled={currentPage === 2}
            class:opacity-50={currentPage === 2}
          >
            <span class="hidden sm:inline">Teachers</span>
            <span class="sm:hidden">Teach</span>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 md:h-5 md:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </button>
        </div>
        
            </div>
            
          </div>
        </div>
        <!-- Green background extension to footer -->
        <div class="bg-gradient-to-r from-green-600 to-green-700 flex-grow"></div>
      </div>
      {/if}
    </main>
    
    <!-- Admin Login Modal Component -->
    <SimpleAdminLogin 
      open={showAdminModal}
      close={() => {
        showAdminModal = false;
        adminUsername = '';
        adminPassword = '';
        loginError = '';
      }}
    />
    
    <!-- Faculty Edit Modal -->
    {#if showEditModal}
      <div class="fixed inset-0 bg-white/20 backdrop-blur-sm flex items-center justify-center p-3 md:p-4 pt-16 md:pt-20 z-50" 
           role="dialog" 
           aria-modal="true"
           tabindex="-1"
           on:click={(e) => { if (e.target === e.currentTarget) closeEditModal(); }}
           on:keydown={(e) => e.key === 'Escape' && closeEditModal()}
           transition:fade={{ duration: 200 }}>
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full max-h-[85vh] md:max-h-[80vh] overflow-y-auto faculty-edit-modal" 
             role="document"
             transition:fly={{ y: 50, duration: 300 }}>
          <div class="bg-gradient-to-r from-green-600 to-green-700 text-white p-4 md:p-6 rounded-tl-2xl">
            <div class="flex justify-between items-center">
              <h3 class="text-lg md:text-xl font-bold">
                {isCreatingNew ? 'Add New Faculty' : 'Edit Faculty'}
              </h3>
              <button 
                on:click={closeEditModal} 
                class="text-white hover:text-green-200 transition-colors"
                aria-label="Close modal">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>
          
          <form on:submit|preventDefault={saveFaculty} class="p-4 md:p-6 space-y-3 md:space-y-4">
            <div>
              <label for="faculty-name" class="block text-sm font-medium text-gray-700 mb-2">Name</label>
              <input
                id="faculty-name"
                type="text"
                bind:value={editForm.name}
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm md:text-base"
                placeholder="Enter faculty name"
              />
            </div>
            
            <div>
              <label for="faculty-role" class="block text-sm font-medium text-gray-700 mb-2">Role</label>
              <select
                id="faculty-role"
                bind:value={editForm.role}
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm md:text-base"
              >
                <option value="">Select Role</option>
                <option value="Principal">Principal</option>
                <option value="Assistant Principal">Assistant Principal</option>
                <option value="Master Teacher">Master Teacher</option>
                <option value="Teacher">Teacher</option>
                <option value="Laboratory Technician">Laboratory Technician</option>
                <option value="Maintenance Supervisor">Maintenance Supervisor</option>
                <option value="Custodian">Custodian</option>
                <option value="Groundskeeper">Groundskeeper</option>
                <option value="Facilities Manager">Facilities Manager</option>
                <option value="Security Guard">Security Guard</option>
              </select>
            </div>
            
            <div>
              <label for="faculty-department" class="block text-sm font-medium text-gray-700 mb-2">Department</label>
              <select
                id="faculty-department"
                bind:value={editForm.department}
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm md:text-base"
              >
                <option value="">Select Department</option>
                <option value="Administration">Administration</option>
                <option value="Mathematics">Mathematics</option>
                <option value="Science">Science</option>
                <option value="English">English</option>
                <option value="Filipino">Filipino</option>
                <option value="Social Studies">Social Studies</option>
                <option value="MAPEH">MAPEH</option>
                <option value="TLE">TLE</option>
                <option value="Maintenance">Maintenance</option>
              </select>
            </div>
            
            <div>
              <label for="faculty-email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
              <input
                id="faculty-email"
                type="email"
                bind:value={editForm.email}
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm md:text-base"
                placeholder="Enter email address"
              />
            </div>
            
            <div>
              <label for="faculty-number" class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
              <input
                id="faculty-number"
                type="tel"
                bind:value={editForm.number}
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm md:text-base"
                placeholder="Enter phone number"
              />
            </div>
            
            <!-- Profile Image Upload -->
            <div>
              <label for="faculty-image" class="block text-sm font-medium text-gray-700 mb-2">Profile Image</label>
              <div class="space-y-3">
                <!-- Current Image Preview -->
                {#if editForm.image_url}
                  <div class="flex items-center gap-3">
                    <img 
                      src={editForm.image_url} 
                      alt="Current profile" 
                      class="w-12 h-12 md:w-16 md:h-16 rounded-full object-cover border-2 border-gray-200"
                    />
                    <div class="flex-1">
                      <p class="text-sm text-gray-600">Current profile image</p>
                      <button
                        type="button"
                        on:click={() => {editForm.image_url = ""}}
                        class="text-xs text-red-600 hover:text-red-700 underline"
                      >
                        Remove image
                      </button>
                    </div>
                  </div>
                {/if}
                
                <!-- File Upload -->
                <div>
                  <input
                    id="faculty-image"
                    type="file"
                    accept="image/*"
                    on:change={handleImageUpload}
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 file:mr-3 file:py-1 file:px-3 file:rounded file:border-0 file:text-sm file:bg-green-50 file:text-green-700 hover:file:bg-green-100 text-sm"
                  />
                  <p class="text-xs text-gray-500 mt-1">Upload a profile image (JPG, PNG, GIF - Max 5MB)</p>
                </div>
                
                {#if uploadingImage}
                  <div class="flex items-center gap-2 text-green-600">
                    <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-sm">Uploading image...</span>
                  </div>
                {/if}
              </div>
            </div>
            
            <div class="flex gap-2 md:gap-3 pt-4">
              <button
                type="button"
                on:click={closeEditModal}
                class="flex-1 bg-gray-100 text-gray-700 px-3 py-2 rounded-lg hover:bg-gray-200 transition-colors text-sm md:text-base"
              >
                Cancel
              </button>
              <button
                type="submit"
                disabled={isUpdating}
                class="flex-1 bg-green-600 text-white px-3 py-2 rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2 text-sm md:text-base"
              >
                {#if isUpdating}
                  <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  {isCreatingNew ? 'Adding...' : 'Updating...'}
                {:else}
                  {isCreatingNew ? 'Add Faculty' : 'Update Faculty'}
                {/if}
              </button>
            </div>
          </form>
        </div>
      </div>
    {/if}
    
    <!-- Mobile Profile Modal -->
    {#if showMobileProfileModal && selectedProfile}
      <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 md:hidden" 
           role="dialog" 
           aria-modal="true"
           tabindex="-1"
           on:click={(e) => { if (e.target === e.currentTarget) closeMobileProfileModal(); }}
           on:keydown={(e) => e.key === 'Escape' && closeMobileProfileModal()}
           transition:fade={{ duration: 200 }}>
        <div class="bg-white rounded-2xl shadow-2xl mx-4 w-full max-w-sm" 
             role="document"
             transition:fly={{ y: 50, duration: 300 }}>
          <!-- Header with department color -->
          <div class="rounded-t-2xl p-6 text-white" 
               style="background: linear-gradient(135deg, {selectedProfile.departmentColor}, {selectedProfile.departmentColor}CC);">
            <div class="flex justify-between items-start">
              <div class="flex-1">
                <h3 class="text-xl font-bold mb-2">{selectedProfile.name}</h3>
                <p class="text-white/90 text-sm">{selectedProfile.role}</p>
              </div>
              <button
                on:click={closeMobileProfileModal}
                class="text-white hover:text-white/80 transition-colors p-1 ml-2"
                aria-label="Close profile"
              >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>
          
          <!-- Profile content -->
          <div class="p-6">
            <!-- Contact Information -->
            {#if selectedProfile.email || selectedProfile.number}
              <div class="space-y-3">
                {#if selectedProfile.email}
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center" 
                         style="background-color: {selectedProfile.departmentColor}20;">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" style="color: {selectedProfile.departmentColor};" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M14.243 5.757a6 6 0 10-.986 9.284 1 1 0 111.087 1.678A8 8 0 1118 10a3 3 0 01-4.8 2.401A4 4 0 1114 10a1 1 0 102 0c0-1.537-.586-3.07-1.757-4.243zM12 10a2 2 0 10-4 0 2 2 0 004 0z" clip-rule="evenodd" />
                      </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                      <p class="text-sm text-gray-600">Email</p>
                      <p class="text-sm font-medium text-gray-900 break-all">{selectedProfile.email}</p>
                    </div>
                  </div>
                {/if}
                
                {#if selectedProfile.number}
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center" 
                         style="background-color: {selectedProfile.departmentColor}20;">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" style="color: {selectedProfile.departmentColor};" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z" />
                      </svg>
                    </div>
                    <div class="flex-1">
                      <p class="text-sm text-gray-600">Phone</p>
                      <p class="text-sm font-medium text-gray-900">{selectedProfile.number}</p>
                    </div>
                  </div>
                {/if}
              </div>
            {:else}
              <div class="text-center py-4">
                <div class="w-16 h-16 rounded-full mx-auto mb-3 flex items-center justify-center" 
                     style="background-color: {selectedProfile.departmentColor}20;">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" style="color: {selectedProfile.departmentColor};" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                  </svg>
                </div>
                <p class="text-sm text-gray-500 italic">No contact information available</p>
              </div>
            {/if}
            
            <!-- Close button -->
            <button
              on:click={closeMobileProfileModal}
              class="w-full mt-6 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-3 px-4 rounded-lg transition-colors"
            >
              Close
            </button>
          </div>
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
  </div>
</div>  