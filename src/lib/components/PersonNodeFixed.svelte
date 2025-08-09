<script>
	import { createEventDispatcher } from 'svelte';
	const dispatch = createEventDispatcher();

	export let name = '';
	export let role = '';
	export let email = '';
	export let number = '';
	export let id = '';
	export let department = 'General';

	// Department-based color mapping for visual hierarchy
	const departmentColors = {
		'Admin': '#1e40af',
		'Science': '#059669',
		'Mathematics': '#7c3aed',
		'English': '#dc2626',
		'Filipino': '#ea580c',
		'Social Studies': '#0369a1',
		'MAPEH': '#16a34a',
		'TLE': '#ca8a04',
		'Special Education': '#be123c',
		'ESP': '#86198f',
		'General': '#374151'
	};

	$: departmentColor = departmentColors[department] || departmentColors['General'];
	$: isLeadership = role.includes('Principal') || role.includes('Head') || role.includes('Coordinator');
	$: nodeGlow = isLeadership ? 'shadow-md shadow-yellow-300/30' : 'shadow-sm shadow-gray-200/50';

	function handleNodeClick() {
		dispatch('nodeClick', { id, name, role, email, number, department });
	}
</script>

<div class="faculty-node group relative p-3 rounded-xl bg-gradient-to-br from-green-50 to-green-100 hover:from-green-100 hover:to-green-200 transition-all duration-300 transform hover:scale-105 cursor-pointer border border-green-200/50 hover:border-green-300" on:click={handleNodeClick} role="button" tabindex="0" on:keydown={(e) => e.key === 'Enter' && handleNodeClick()}>
  <div class="relative mb-2">
    <!-- Profile Picture Circle with department color accent and leadership glow -->
    <div 
      class="w-[90px] h-[90px] rounded-full flex items-center justify-center shadow-lg {isLeadership ? 'shadow-yellow-200/50' : ''}"
      style="background: linear-gradient(135deg, {departmentColor}, {departmentColor}CC);"
    >
      <div class="w-[84px] h-[84px] rounded-full bg-white flex items-center justify-center overflow-hidden border-2 border-white {nodeGlow}">
        <!-- Default avatar icon -->
        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
        </svg>
      </div>
    </div>
    
    <!-- Badge Accent with different icons based on role -->
    <div 
      class="absolute -right-1 -bottom-1 w-6 h-6 rounded-full border-2 border-white shadow-md flex items-center justify-center"
      style="background-color: {departmentColor};"
    >
      {#if role.includes("Principal")}
        <!-- Crown icon for Principal -->
        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-white" viewBox="0 0 20 20" fill="currentColor">
          <path d="M5 4a1 1 0 00-2 0v7.268a2 2 0 000 3.464V16a1 1 0 102 0v-1.268a2 2 0 000-3.464V4zM11 4a1 1 0 10-2 0v1.268a2 2 0 000 3.464V16a1 1 0 102 0V8.732a2 2 0 000-3.464V4zM16 3a1 1 0 011 1v7.268a2 2 0 010 3.464V16a1 1 0 11-2 0v-1.268a2 2 0 010-3.464V4a1 1 0 011-1z" />
        </svg>
      {:else if role.includes("Master Teacher")}
        <!-- Star icon for Master Teachers -->
        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-white" viewBox="0 0 20 20" fill="currentColor">
          <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
        </svg>
      {:else}
        <!-- Education icon for regular teachers -->
        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-white" viewBox="0 0 20 20" fill="currentColor">
          <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z" />
        </svg>
      {/if}
    </div>
  </div>
  
  <!-- Text Content with special styling for leadership roles -->
  <div class="text-center max-w-[120px]">
    <h3 class="font-bold text-sm {isLeadership ? 'text-green-800' : 'text-green-900'} leading-tight">{name}</h3>
    <p class="text-xs {isLeadership ? 'text-green-600 font-medium' : 'text-green-700'} mt-1">{role}</p>
  </div>
  
  <!-- Tooltip on hover - visible on larger screens -->
  <div class="absolute opacity-0 group-hover:opacity-100 pointer-events-none bottom-full mb-2 bg-white/90 backdrop-blur-sm rounded-lg p-3 shadow-lg border border-green-200 w-48 transition-opacity duration-200 hidden md:block">
    <h4 class="font-bold text-green-900">{name}</h4>
    <p class="text-sm text-green-700">{role}</p>
    {#if email}
      <div class="mt-2 text-xs flex items-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 text-green-600" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M14.243 5.757a6 6 0 10-.986 9.284 1 1 0 111.087 1.678A8 8 0 1118 10a3 3 0 01-4.8 2.401A4 4 0 1114 10a1 1 0 102 0c0-1.537-.586-3.07-1.757-4.243zM12 10a2 2 0 10-4 0 2 2 0 004 0z" clip-rule="evenodd" />
        </svg>
        <span class="break-all">{email}</span>
      </div>
    {/if}
    {#if number}
      <div class="mt-1 text-xs flex items-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 text-green-600" viewBox="0 0 20 20" fill="currentColor">
          <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z" />
        </svg>
        <span>{number}</span>
      </div>
    {/if}
    {#if !email && !number}
      <div class="mt-2 text-xs text-gray-500 italic">
        No contact information available
      </div>
    {/if}
  </div>
</div>
