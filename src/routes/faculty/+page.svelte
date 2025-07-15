<!-- src/routes/+page.svelte -->
<script lang="ts">
  import PersonNode from "$lib/components/PersonNode.svelte";
  import Navbar from "$lib/components/navbar.svelte";
  import { fly, fade, slide } from 'svelte/transition';
  import { onMount } from 'svelte';
  
  let visible = false;
  let searchQuery = "";
  let selectedDepartment = "All";
  let viewMode = "grid"; // "grid" or "tree"
  
  // Admin modal state
  let showAdminModal = false;
  let adminEmail = "";
  let adminPassword = "";
  let loginError = "";
  
  // Window width for responsive design
  let windowWidth = 1024; // Default to desktop width
  
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
    "TLE"
  ];
  
  // Updated facultyData with proper structure
  const facultyData = [
    { name: "Dr. Maria Santos", role: "Principal", department: "Administration" },
    { name: "Mr. Juan Dela Cruz", role: "Assistant Principal", department: "Administration" },
    // Master Teachers
    { name: "Ms. Ana Reyes", role: "Master Teacher", department: "Mathematics" },
    { name: "Dr. Elena Garcia", role: "Master Teacher", department: "Science" },
    { name: "Ms. Patricia Reyes", role: "Master Teacher", department: "English" },
    { name: "Ms. Juana Marcos", role: "Master Teacher", department: "Filipino" },
    { name: "Mr. Roberto Villanueva", role: "Master Teacher", department: "Social Studies" },
    { name: "Mr. Jose Bautista", role: "Master Teacher", department: "MAPEH" },
    { name: "Ms. Carolina Dizon", role: "Master Teacher", department: "TLE" },
    // Regular Teachers
    { name: "Mr. Paolo Mendoza", role: "Teacher", department: "Mathematics" },
    { name: "Ms. Clara Ocampo", role: "Teacher", department: "Mathematics" },
    { name: "Mr. Felix Ramos", role: "Teacher", department: "Mathematics" },
    { name: "Mr. Diego Velasco", role: "Teacher", department: "Science" },
    { name: "Ms. Camille Santos", role: "Teacher", department: "Science" },
    { name: "Mr. Gabriel Lim", role: "Laboratory Technician", department: "Science" },
    { name: "Mr. Ramon Torres", role: "Teacher", department: "English" },
    { name: "Ms. Sofia Luna", role: "Teacher", department: "English" },
    { name: "Mr. Antonio Morales", role: "Teacher", department: "Filipino" },
    { name: "Ms. Rosario Cruz", role: "Teacher", department: "Filipino" },
    { name: "Ms. Leticia Torres", role: "Teacher", department: "Social Studies" },
    { name: "Ms. Michelle Fernandez", role: "Teacher", department: "MAPEH" },
    { name: "Mr. Ricardo Santos", role: "Teacher", department: "MAPEH" },
    { name: "Mr. Eduardo Villanueva", role: "Teacher", department: "TLE" },
    { name: "Ms. Teresa Aquino", role: "Teacher", department: "TLE" }
  ];
  
  // Helper function to get the principal
  function getPrincipal() {
    return facultyData.find(f => f.role === "Principal");
  }
  
  // Helper function to get the assistant principal
  function getAssistantPrincipal() {
    return facultyData.find(f => f.role === "Assistant Principal");
  }
  
  // Helper function to get master teachers
  function getMasterTeachers() {
    return facultyData.filter(f => f.role === "Master Teacher");
  }
  
  // Helper function to get regular teachers by department
  function getTeachersByDepartment(department: string) {
    return facultyData.filter(f => 
      f.department === department && 
      f.role !== "Master Teacher" && 
      f.role !== "Principal" && 
      f.role !== "Assistant Principal"
    );
  }
  
  // Filter faculty based on search and department selection
  $: filteredFaculty = facultyData.filter(faculty => {
    const matchesSearch = faculty.name.toLowerCase().includes(searchQuery.toLowerCase()) || 
                         faculty.role.toLowerCase().includes(searchQuery.toLowerCase());
    const matchesDepartment = selectedDepartment === "All" || faculty.department === selectedDepartment;
    return matchesSearch && matchesDepartment;
  });
  
  // Automatically switch to grid view on smaller screens
  $: if (windowWidth < 768 && viewMode === "tree") {
    viewMode = "grid";
  }
  
  // Admin login function
  async function handleAdminLogin() {
    if (!adminEmail || !adminPassword) {
      loginError = "Please enter both username and password";
      return;
    }
    
    try {
      const response = await fetch('http://localhost:80/ncnhs-api/admin-login.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          username: adminEmail, // Using adminEmail variable for username
          password: adminPassword
        })
      });
      
      const data = await response.json();
      
      if (data.success) {
        loginError = "";
        localStorage.setItem('admin_token', data.token);
        alert("Admin access granted! Welcome " + data.admin.username);
        showAdminModal = false;
        adminEmail = "";
        adminPassword = "";
      } else {
        loginError = data.error || "Login failed";
      }
    } catch (error) {
      loginError = "Connection error. Please try again.";
    }
  }
  
  function closeAdminModal() {
    showAdminModal = false;
    adminEmail = "";
    adminPassword = "";
    loginError = "";
  }
  
  // Handle admin button click from navbar
  function handleAdminClick() {
    showAdminModal = true;
  }
  
  onMount(() => {
    visible = true;
  });
</script>

<svelte:window bind:innerWidth={windowWidth} />

<!-- Main container with background image and pattern overlay -->
<div class="relative min-h-screen bg-cover bg-center font-sans" style="background-image: url('/ncnhs.jpg');">
  <!-- Modern Gradient Overlay with Pattern -->
  <div class="absolute inset-0 bg-gradient-to-b from-green-900/40 via-green-800/20 to-yellow-500/30 mix-blend-overlay"></div>
  <div class="absolute inset-0 bg-[url('/subtle-pattern.png')] opacity-10"></div>

  <!-- Page Content (positioned above overlay) -->
  <div class="relative z-10 flex min-h-screen flex-col">
    <!-- Navbar Component -->
    <Navbar on:adminClick={handleAdminClick} />
    
    <!-- Main Content Area -->
    <main class="flex flex-grow flex-col items-center p-4 md:p-8">
      <!-- School Logo - Modernized -->
      {#if visible}
      <div class="my-6 md:my-8" in:fade={{ duration: 1000 }}>
        <div class="flex h-32 w-32 md:h-40 md:w-40 items-center justify-center rounded-full bg-gradient-to-br from-yellow-300 to-yellow-500 p-1 shadow-xl">
          <div class="flex h-full w-full items-center justify-center rounded-full bg-white text-center shadow-inner">
            <img src="/logo.png" alt="School Logo" class="h-full w-full rounded-full object-cover" />
          </div>
        </div>
      </div>
      {/if}
      
      <!-- Modern Faculty Container with Glassmorphism -->
      {#if visible}
      <div class="mb-12 w-full max-w-6xl rounded-3xl border-8 border-yellow-400/50 bg-white/90 p-4 md:p-6 lg:p-10 shadow-2xl backdrop-blur-md" 
           in:fly={{ y: 50, duration: 1000, delay: 300 }}>
        <h2 class="text-center text-xl md:text-2xl lg:text-3xl font-bold text-green-800 mb-4 md:mb-6">School Faculty</h2>
        
        <!-- Search and Filter Controls -->
        <div class="mb-6 md:mb-8 flex flex-col md:flex-row gap-3 md:gap-4 justify-between">
          <!-- Search Box -->
          <div class="relative w-full md:w-1/2">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
              <svg class="w-4 h-4 text-green-800" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
              </svg>
            </div>
            <input 
              type="search" 
              bind:value={searchQuery}
              class="block w-full p-2 md:p-3 pl-10 text-sm text-green-900 border border-green-300 rounded-lg bg-white/80 focus:ring-green-500 focus:border-green-500" 
              placeholder="Search for faculty members..." 
            />
          </div>
          
          <div class="flex gap-2 md:gap-3">
            <!-- Department Filter -->
            <div class="w-full">
              <select 
                bind:value={selectedDepartment}
                class="bg-white/80 border border-green-300 text-green-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2 md:p-3"
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
                class={`px-3 py-2 text-sm font-medium ${viewMode === 'grid' ? 'bg-green-600 text-white' : 'bg-white/80 text-green-900 hover:bg-green-100'}`}
                on:click={() => viewMode = 'grid'}
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6zM14 6a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2a2 2 0 0 1-2 2h-2a2 2 0 0 1-2-2V6zM4 16a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-2zM14 16a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2a2 2 0 0 1-2 2h-2a2 2 0 0 1-2-2v-2z" />
                </svg>
              </button>
              <button 
                class={`px-3 py-2 text-sm font-medium ${viewMode === 'tree' ? 'bg-green-600 text-white' : 'bg-white/80 text-green-900 hover:bg-green-100'}`}
                on:click={() => viewMode = 'tree'}
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l9 6M3 12l9 6M21 6l-9 6M21 12l-9 6" />
                </svg>
              </button>
            </div>
            {/if}
          </div>
        </div>
        
        <!-- Dynamic Faculty View -->
        {#if viewMode === 'grid' || windowWidth < 768}
          <!-- Grid View -->
          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6 lg:gap-8">
            {#each filteredFaculty as faculty, i}
              <div 
                class="flex justify-center" 
                in:fly={{y: 20, duration: 300, delay: 100 + (i * 50)}}
              >
                <PersonNode 
                  name={faculty.name} 
                  role={faculty.department !== "Administration" ? `${faculty.role} - ${faculty.department}` : faculty.role} 
                  scale={0.9}
                  departmentColor={
                    faculty.department === "Administration" ? "#059669" : 
                    faculty.department === "Mathematics" ? "#3B82F6" : 
                    faculty.department === "Science" ? "#8B5CF6" : 
                    faculty.department === "English" ? "#EC4899" : 
                    faculty.department === "Filipino" ? "#F59E0B" : 
                    faculty.department === "Social Studies" ? "#10B981" : 
                    faculty.department === "MAPEH" ? "#EF4444" : 
                    faculty.department === "TLE" ? "#6366F1" : "#6B7280"
                  }
                />
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
                  on:click={() => {
                    searchQuery = "";
                    selectedDepartment = "All";
                  }}
                >
                  Reset Filters
                </button>
              </div>
            {/if}
          </div>
        {:else}
          <!-- Hierarchical View (No Lines) -->
          <div class="w-full space-y-8 md:space-y-12">
            <!-- Level 1: Principal -->
            {#if getPrincipal()}
              <div class="flex justify-center" in:fly={{ y: 30, duration: 600, delay: 300 }}>
                <PersonNode 
                  name={getPrincipal()?.name || ""} 
                  role={getPrincipal()?.role || ""} 
                  departmentColor="#059669"
                  scale={1.3}
                />
              </div>
            {/if}
            
            <!-- Level 2: Assistant Principal -->
            {#if getAssistantPrincipal()}
              <div class="flex justify-center" in:fly={{ y: 30, duration: 600, delay: 400 }}>
                <PersonNode 
                  name={getAssistantPrincipal()?.name || ""} 
                  role={getAssistantPrincipal()?.role || ""} 
                  departmentColor="#059669"
                  scale={1.1}
                />
              </div>
            {/if}

            <!-- Level 3: Master Teachers -->
            {#if getMasterTeachers().length > 0}
              <div class="space-y-6">
                <h3 class="text-center text-lg md:text-xl font-semibold text-green-800">Master Teachers</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 md:gap-8">
                  {#each getMasterTeachers() as master, i}
                    <div class="flex justify-center" in:fly={{ y: 30, duration: 600, delay: 500 + (i * 100) }}>
                      <PersonNode 
                        name={master.name} 
                        role={`Master Teacher - ${master.department}`} 
                        departmentColor={
                          master.department === "Mathematics" ? "#3B82F6" : 
                          master.department === "Science" ? "#8B5CF6" : 
                          master.department === "English" ? "#EC4899" : 
                          master.department === "Filipino" ? "#F59E0B" : 
                          master.department === "Social Studies" ? "#10B981" : 
                          master.department === "MAPEH" ? "#EF4444" : 
                          master.department === "TLE" ? "#6366F1" : "#6B7280"
                        }
                        scale={1.0}
                      />
                    </div>
                  {/each}
                </div>
              </div>
            {/if}

            <!-- Level 4: Teachers by Department -->
            <div class="space-y-8 md:space-y-12">
              <h1 class="text-center text-lg md:text-3xl font-semibold text-green-800">Department Faculty</h1>
              {#each getMasterTeachers() as master, deptIndex}
                {@const teachers = getTeachersByDepartment(master.department)}
                {#if teachers.length > 0}
                  <div class="space-y-4" in:fly={{ y: 20, duration: 500, delay: 800 + (deptIndex * 200) }}>
                    <!-- Department Header -->
                    <div class="text-center">
                      <h4 class="text-base md:text-lg font-medium text-green-700 mb-1">{master.department} Department</h4>
                      <div class="flex justify-center">
                        <div class="h-1 w-20 rounded-full" 
                             style={`background-color: ${
                               master.department === "Administration" ? "#FFFF00" : 
                              master.department  === "Mathematics" ? "#3B82F6" : 
                              master.department  === "Science" ? "#008000" : 
                              master.department === "English" ? "#0000FF" : 
                              master.department === "Filipino" ? "#964B00" : 
                              master.department === "Social Studies" ? "#10B981" : 
                              master.department === "MAPEH" ? "#B200ED" : 
                              master.department === "TLE" ? "#808080" : "#6B7280"
                             }`}></div>
                      </div>
                    </div>
                    
                    <!-- Department Teachers -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 md:gap-6">
                      {#each teachers as teacher, j}
                        <div class="flex justify-center" in:fly={{ y: 20, duration: 400, delay: 900 + (deptIndex * 200) + (j * 50) }}>
                          <PersonNode 
                            name={teacher.name} 
                            role={teacher.role} 
                            scale={0.85}
                            departmentColor={
                              teacher.department === "Administration" ? "#FFFF00" : 
                              teacher.department === "Mathematics" ? "#3B82F6" : 
                              teacher.department === "Science" ? "#008000" : 
                              teacher.department === "English" ? "#0000FF" : 
                              teacher.department === "Filipino" ? "#964B00" : 
                              teacher.department === "Social Studies" ? "#10B981" : 
                              teacher.department === "MAPEH" ? "#B200ED" : 
                              teacher.department === "TLE" ? "#808080" : "#6B7280"
                            }
                          />
                        </div>
                      {/each}
                    </div>
                  </div>
                {/if}
              {/each}
            </div>

            <!-- Hierarchy Legend -->
            <div class="flex justify-center mt-8">
              <div class="bg-white/90 backdrop-blur-sm p-4 rounded-lg border border-green-200 shadow-lg">
                <h4 class="text-sm font-semibold text-green-800 mb-3 text-center">Organizational Hierarchy</h4>
                <div class="grid grid-cols-2 gap-x-6 gap-y-2 text-xs">
                  <div class="flex items-center gap-2">
                    <div class="w-3 h-3 bg-emerald-600 rounded-full"></div>
                    <span class="text-green-700">Principal</span>
                  </div>
                  <div class="flex items-center gap-2">
                    <div class="w-3 h-3 bg-emerald-500 rounded-full"></div>
                    <span class="text-green-700">Asst. Principal</span>
                  </div>
                  <div class="flex items-center gap-2">
                    <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                    <span class="text-green-700">Master Teachers</span>
                  </div>
                  <div class="flex items-center gap-2">
                    <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                    <span class="text-green-700">Teachers</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        {/if}
        
        <!-- Department Legend -->
        <div class="mt-8 md:mt-12 pt-4 md:pt-6 border-t border-green-200">
          <h3 class="text-center text-base md:text-lg font-semibold text-green-800 mb-3 md:mb-4">Department Color Legend</h3>
          <div class="flex flex-wrap justify-center gap-2 md:gap-3">
            {#each departments.filter(d => d !== "All") as department}
              <div class="flex items-center gap-2 px-2 md:px-3 py-1 bg-green-100 rounded-full">
                <div class="w-3 h-3 rounded-full" 
                     style={`background-color: ${
                       department === "Administration" ? "#FFFF00" : 
                       department === "Mathematics" ? "#3B82F6" : 
                       department === "Science" ? "#008000" : 
                       department === "English" ? "#0000FF" : 
                       department === "Filipino" ? "#964B00" : 
                       department === "Social Studies" ? "#10B981" : 
                       department === "MAPEH" ? "#B200ED" : 
                       department === "TLE" ? "#808080" : "#6B7280"
                     }`}></div>
                <span class="text-xs font-medium text-green-900">{department}</span>
              </div>
            {/each}
          </div>
        </div>
      </div>
      {/if}
    </main>
    
    <!-- Admin Login Modal -->
    {#if showAdminModal}
      <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4" 
           in:fade={{ duration: 200 }}
           on:click|self={closeAdminModal}>
        <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md" 
             in:fly={{ y: 50, duration: 300 }}>
          <!-- Modal Header -->
          <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-green-800 flex items-center gap-2">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
              </svg>
              Admin Login
            </h2>
            <button 
              class="text-gray-400 hover:text-gray-600 transition-colors"
              on:click={closeAdminModal}
              aria-label="Close modal"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          
          <!-- Login Form -->
          <form on:submit|preventDefault={handleAdminLogin} class="space-y-4">
            <!-- Email Field -->
            <div>
              <label for="admin-email" class="block text-sm font-medium text-gray-700 mb-2">
                Email Address
              </label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                  </svg>
                </div>
                <input 
                  id="admin-email"
                  type="email" 
                  bind:value={adminEmail}
                  class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors"
                  placeholder="admin@ncnhs.edu.ph"
                  required
                />
              </div>
            </div>
            
            <!-- Password Field -->
            <div>
              <label for="admin-password" class="block text-sm font-medium text-gray-700 mb-2">
                Password
              </label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                  </svg>
                </div>
                <input 
                  id="admin-password"
                  type="password" 
                  bind:value={adminPassword}
                  class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors"
                  placeholder="Enter password"
                  required
                />
              </div>
            </div>
            
            <!-- Error Message -->
            {#if loginError}
              <div class="bg-red-50 border border-red-200 rounded-lg p-3" in:slide={{ duration: 200 }}>
                <div class="flex items-center">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <span class="text-sm text-red-700">{loginError}</span>
                </div>
              </div>
            {/if}
            
            <!-- Action Buttons -->
            <div class="flex gap-3 pt-4">
              <button
                type="button"
                class="flex-1 px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors font-medium"
                on:click={closeAdminModal}
              >
                Cancel
              </button>
              <button
                type="submit"
                class="flex-1 px-4 py-2 text-white bg-gradient-to-r from-green-600 to-green-700 rounded-lg hover:from-green-700 hover:to-green-800 transition-all duration-200 font-medium shadow-md hover:shadow-lg"
              >
                Login
              </button>
            </div>
          </form>
          
          <!-- Help Text -->
          <div class="mt-4 pt-4 border-t border-gray-200">
            <p class="text-xs text-gray-500 text-center">
              For demonstration: admin@ncnhs.edu.ph / admin123
            </p>
          </div>
        </div>
      </div>
    {/if}
    
    <!-- Modern Footer -->
    <footer class="bg-green-900/80 text-white py-3 md:py-4 backdrop-blur-md border-t border-green-700/30">
      <div class="container mx-auto text-center text-xs md:text-sm">
        <p>© {new Date().getFullYear()} New Cabalan National High School. All rights reserved.</p>
        <p class="mt-1 md:mt-2 text-yellow-300/80 text-[10px] md:text-xs">Empowering students through education since 1979</p>
      </div>
    </footer>
  </div>
</div>  