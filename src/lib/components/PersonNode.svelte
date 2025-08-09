<script lang="ts">
  import { createEventDispatcher } from 'svelte';
  import { fly } from 'svelte/transition';
  import { quintOut } from 'svelte/easing';
  
  const dispatch = createEventDispatcher();

  export let name = '';
  export let role = '';
  export let email = '';
  export let number = '';
  export let id = '';
  export let department = 'General';
  export let departmentColor = '';
  export let scale = 1;
  export let imageUrl = ''; // Future-proofing for profile images
  export let isLoggedIn = false; // Admin status passed as prop
  
  // Debug logging
  // $: console.log(`PersonNode [${name}] - isLoggedIn:`, isLoggedIn);

  // Enhanced department color mapping - maintaining existing colors
  function getDepartmentColor(dept: string): string {
    const colorMap: Record<string, string> = {
      'Administration': '#FFFF00', // Keep original yellow
      'Science': '#008000', // Keep original green
      'Mathematics': '#3B82F6', // Keep original blue
      'English': '#FF0000', // Keep original red
      'Filipino': '#964B00', // Keep original brown
      'Social Studies': '#10B981', // Keep original emerald
      'MAPEH': '#B200ED', // Keep original purple
      'TLE': '#000000', // Keep original black
      'Maintenance': '#FF6600', // Keep original orange
      'Admin': '#FFFF00',
      default: '#374151'
    };
    return colorMap[dept] || colorMap.default;
  }

  $: computedColor = departmentColor && departmentColor.trim() !== '' ? departmentColor : getDepartmentColor(department);
  $: isLeadership = role.includes('Principal') || role.includes('Head') || role.includes('Coordinator') || role.includes('Master Teacher');
  $: initials = name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
  
  function handleNodeClick() {
    const detail = { id, name, role, email, number, department, departmentColor: computedColor };
    dispatch('nodeClick', detail);
    dispatch('profileClick', detail);
  }

  function handleEdit(event: Event) {
    event.stopPropagation();
    console.log('Edit button clicked for:', name, 'ID:', id);
    const detail = { id, name, role, email, number, department, departmentColor: computedColor, imageUrl };
    dispatch('editFaculty', detail);
  }

  function handleDelete(event: Event) {
    event.stopPropagation();
    console.log('Delete button clicked for:', name, 'ID:', id);
    const detail = { id, name, role, email, number, department };
    dispatch('deleteFaculty', detail);
  }

  // Determine role icon
  function getRoleIcon(role: string) {
    if (role.includes('Principal')) return 'crown';
    if (role.includes('Master Teacher')) return 'star';
    if (role.includes('Teacher')) return 'academic';
    if (role.includes('Technician')) return 'tool';
    return 'academic';
  }

  $: roleIcon = getRoleIcon(role);
</script>

<style>
  .person-card:hover .hover-overlay {
    opacity: 1 !important;
    transform: translateY(0) !important;
  }
</style>

<div 
  class="person-card group relative transform transition-all duration-300 hover:scale-105 cursor-pointer"
  style="transform: scale({scale}); transform-origin: center; overflow: visible;"
  on:click={handleNodeClick} 
  on:keydown={(e) => e.key === 'Enter' && handleNodeClick()}
  role="button" 
  tabindex="0"
  in:fly={{ y: 20, duration: 600, easing: quintOut }}
>
  <!-- Main Card -->
  <div class="relative bg-white/90 backdrop-blur-sm rounded-2xl p-6 shadow-lg hover:shadow-2xl transition-all duration-300 border border-gray-200/50 hover:border-gray-300/70 group-hover:bg-white min-h-[200px] w-[220px] overflow-hidden">
    
    <!-- Department Color Accent -->
    <div 
      class="absolute top-0 left-0 right-0 h-1 rounded-t-2xl"
      style="background: linear-gradient(90deg, {computedColor}, {computedColor}80);"
    ></div>
    
    <!-- Admin Mode Indicator (top-right corner when logged in) -->
    {#if isLoggedIn}
      <div class="absolute top-2 right-2 opacity-30 group-hover:opacity-60 transition-opacity duration-300">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-500" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
        </svg>
      </div>
    {/if}
    
    <!-- Profile Section -->
    <div class="flex flex-col items-center mb-4">
      
      <!-- Avatar Container -->
      <div class="relative mb-3">
        <!-- Main Avatar Circle -->
        <div 
          class="w-20 h-20 rounded-full flex items-center justify-center text-white font-bold text-lg shadow-lg transition-all duration-300 group-hover:shadow-xl"
          style="background: linear-gradient(135deg, {computedColor}, {computedColor}CC);"
        >
          {#if imageUrl}
            <img src={imageUrl} alt={name} class="w-full h-full rounded-full object-cover" />
          {:else}
            <span class="font-semibold">{initials}</span>
          {/if}
        </div>
        
        <!-- Role Badge -->
        <div 
          class="absolute -bottom-1 -right-1 w-7 h-7 rounded-full border-2 border-white shadow-md flex items-center justify-center transition-transform duration-300 group-hover:scale-110"
          style="background-color: {computedColor};"
        >
          {#if roleIcon === 'crown'}
            <!-- Crown for Principal -->
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M5 4a1 1 0 00-2 0v7.268a2 2 0 000 3.464V16a1 1 0 102 0v-1.268a2 2 0 000-3.464V4zM11 4a1 1 0 10-2 0v1.268a2 2 0 000 3.464V16a1 1 0 102 0V8.732a2 2 0 000-3.464V4zM16 3a1 1 0 011 1v7.268a2 2 0 010 3.464V16a1 1 0 11-2 0v-1.268a2 2 0 010-3.464V4a1 1 0 011-1z" clip-rule="evenodd" />
            </svg>
          {:else if roleIcon === 'star'}
            <!-- Star for Master Teacher -->
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" viewBox="0 0 20 20" fill="currentColor">
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
            </svg>
          {:else if roleIcon === 'tool'}
            <!-- Tool for Technician -->
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M12.316 3.051a1 1 0 01.633 1.265l-4 12a1 1 0 11-1.898-.632l4-12a1 1 0 011.265-.633zM5.707 6.293a1 1 0 010 1.414L3.414 10l2.293 2.293a1 1 0 11-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0zm8.586 0a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 11-1.414-1.414L16.586 10l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
          {:else}
            <!-- Academic cap for Teachers -->
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" viewBox="0 0 20 20" fill="currentColor">
              <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z" />
            </svg>
          {/if}
        </div>

        <!-- Online/Status Indicator -->
        <div class="absolute -top-1 -right-1 w-4 h-4 bg-green-400 border-2 border-white rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
      </div>
      
      <!-- Name and Role -->
      <div class="text-center space-y-1">
        <h3 class="font-bold text-gray-900 text-sm leading-tight group-hover:text-gray-800 transition-colors duration-300">
          {name}
        </h3>
        <p class="text-xs text-gray-600 font-medium group-hover:text-gray-700 transition-colors duration-300">
          {role}
        </p>
        
        <!-- Department Tag -->
        <div class="inline-flex items-center gap-1 px-2 py-1 bg-gray-100 rounded-full text-xs text-gray-700 mt-2 group-hover:bg-gray-200 transition-colors duration-300">
          <div 
            class="w-2 h-2 rounded-full"
            style="background-color: {computedColor};"
          ></div>
          <span class="font-medium">{department}</span>
        </div>
        
        <!-- Admin Controls Hint (only show when logged in) -->
        {#if isLoggedIn}
          <div class="mt-2 opacity-50 group-hover:opacity-0 transition-opacity duration-300">
            <div class="flex items-center justify-center gap-1 text-xs text-gray-500">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.293l-3-3a1 1 0 00-1.414-1.414L9 5.586 7.707 4.293a1 1 0 00-1.414 1.414L8.586 8H4a1 1 0 100 2h4.586l-2.293 2.293a1 1 0 101.414 1.414L9 12.414l.293.293a1 1 0 001.414-1.414L8.414 10H13a1 1 0 100-2H8.414l2.293-2.293z" clip-rule="evenodd" />
              </svg>
              <span>Hover to edit</span>
            </div>
          </div>
        {/if}
      </div>
    </div>
    
    <!-- Contact Information & Admin Controls (On Hover) -->
    <div class="hover-overlay absolute inset-x-4 bottom-4 bg-white rounded-xl p-3 shadow-xl border border-gray-200 z-30"
         style="
           opacity: 0;
           transform: translateY(20px);
           transition: all 0.3s ease-in-out;
           pointer-events: auto;
           box-sizing: border-box;
         ">
      {#if email || number}
        <div class="space-y-2 mb-3">
          <h4 class="text-xs font-semibold text-gray-700 mb-2">Contact Information</h4>
          
          {#if email}
            <div class="flex items-center gap-2 text-xs">
              <div 
                class="w-5 h-5 rounded-full flex items-center justify-center"
                style="background-color: {computedColor}20;"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" style="color: {computedColor};" viewBox="0 0 20 20" fill="currentColor">
                  <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                  <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                </svg>
              </div>
              <span class="text-gray-600 truncate flex-1">{email}</span>
            </div>
          {/if}
          
          {#if number}
            <div class="flex items-center gap-2 text-xs">
              <div 
                class="w-5 h-5 rounded-full flex items-center justify-center"
                style="background-color: {computedColor}20;"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" style="color: {computedColor};" viewBox="0 0 20 20" fill="currentColor">
                  <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z" />
                </svg>
              </div>
              <span class="text-gray-600">{number}</span>
            </div>
          {/if}
        </div>
      {:else}
        <div class="text-center py-2 mb-3">
          <div 
            class="w-8 h-8 rounded-full mx-auto mb-2 flex items-center justify-center"
            style="background-color: {computedColor}20;"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" style="color: {computedColor};" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
            </svg>
          </div>
          <p class="text-xs text-gray-500">No contact info</p>
        </div>
      {/if}

      <!-- Admin Controls (Only show when logged in) -->
      {#if isLoggedIn}
        <div class="flex gap-1.5 pt-2 border-t border-gray-100 justify-center">
          <button
            on:click={handleEdit}
            class="bg-blue-500 hover:bg-blue-600 text-white text-xs px-2.5 py-1 rounded-md transition-colors duration-200 flex items-center justify-center gap-1"
            title="Edit Faculty"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-2.5 w-2.5" viewBox="0 0 20 20" fill="currentColor">
              <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
            </svg>
            Edit
          </button>
          
          <button
            on:click={handleDelete}
            class="bg-red-500 hover:bg-red-600 text-white text-xs px-2.5 py-1 rounded-md transition-colors duration-200 flex items-center justify-center gap-1"
            title="Delete Faculty"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-2.5 w-2.5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 006 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 10.23 1.482l.149-.022.841 10.518A2.75 2.75 0 007.596 19h4.807a2.75 2.75 0 002.742-2.53l.841-10.52.149.023a.75.75 0 00.23-1.482A41.03 41.03 0 0014 4.193V3.75A2.75 2.75 0 0011.25 1h-2.5zM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4zM8.58 7.72a.75.75 0 00-1.5.06l.3 7.5a.75.75 0 101.5-.06l-.3-7.5zm4.34.06a.75.75 0 10-1.5-.06l-.3 7.5a.75.75 0 101.5.06l.3-7.5z" clip-rule="evenodd" />
            </svg>
            Delete
          </button>
        </div>
      {:else}
        <div class="text-center py-2">
          <div 
            class="w-8 h-8 rounded-full mx-auto mb-2 flex items-center justify-center"
            style="background-color: {computedColor}20;"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" style="color: {computedColor};" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
            </svg>
          </div>
          <p class="text-xs text-gray-500">Hover to see contact info</p>
        </div>
      {/if}
    </div>
    
    <!-- Hover Glow Effect -->
    <div class="absolute inset-0 rounded-2xl bg-gradient-to-br opacity-0 group-hover:opacity-5 transition-opacity duration-300 pointer-events-none"
         style="background: linear-gradient(135deg, {computedColor}, transparent);"></div>
  </div>
</div>
