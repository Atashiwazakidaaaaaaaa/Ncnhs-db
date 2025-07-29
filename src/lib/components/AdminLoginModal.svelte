<script lang="ts">
  import { fly, fade, slide } from 'svelte/transition';
  import { createEventDispatcher } from 'svelte';

  const dispatch = createEventDispatcher();

  // Props
  export let showModal = false;
  export let username = "";
  export let password = "";
  export let loginError = "";

  // Admin login function
  async function handleAdminLogin() {
    if (!username || !password) {
      loginError = "Please enter both username and password";
      return;
    }
    
    // Clear previous errors
    loginError = "";
    
    try {
      console.log('Attempting admin login with:', { username });
      
      // Use the correct API endpoint
      const response = await fetch('http://localhost:80/test-login.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: JSON.stringify({
          username,
          password
        })
      });

      console.log('Response status:', response.status);
      console.log('Response headers:', response.headers);

      // Check if response is ok
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      // Parse JSON response
      const data = await response.json();
      console.log('API response:', data);
      
      if (data.success) {
        // Success - API returned success: true
        document.cookie = "auth=true; path=/; max-age=3600"; // Set cookie for 1 hour
        
        // Dispatch success event
        dispatch('loginSuccess', {
          token: data.token,
          admin: data.admin
        });
        
        closeModal();
      } else {
        // API returned success: false with error message
        loginError = data.error || "Login failed";
      }
    } catch (error) {
      console.error('Login error:', error);
      // Handle network/connection errors
      if (error instanceof Error && error.message.includes('Failed to fetch')) {
        loginError = "Cannot connect to server. Please check if MAMP is running.";
      } else if (error instanceof Error) {
        loginError = `Connection error: ${error.message}`;
      } else {
        loginError = "Connection error. Please try again.";
      }
    }
  }
  
  function closeModal() {
    showModal = false;
    username = "";
    password = "";
    loginError = "";
    
    // Dispatch close event
    dispatch('close');
  }
</script>

<!-- Admin Login Modal -->
{#if showModal}
  <div 
    class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4" 
    in:fade={{ duration: 200 }}
    on:click|self={closeModal}
    on:keydown={(e) => e.key === 'Escape' && closeModal()}
    role="dialog"
    aria-modal="true"
    aria-labelledby="modal-title"
    tabindex="-1"
  >
    <div 
      class="bg-white rounded-3xl shadow-2xl p-8 w-full max-w-md relative" 
      in:fly={{ y: 50, duration: 300 }}
    >
      
      <!-- Close Button -->
      <button 
        class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors z-10"
        on:click={closeModal}
        aria-label="Close modal"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>

      <!-- School Logo -->
      <div class="flex justify-center mb-6">
        <div class="w-24 h-24 flex items-center justify-center">
          <img src="/logo.png" alt="School Logo" class="w-full h-full object-contain" />
        </div>
      </div>
      
      <!-- Hidden Modal Title for Accessibility -->
      <h2 id="modal-title" class="sr-only">Admin Login</h2>
      
      <!-- Login Form -->
      <form on:submit|preventDefault={handleAdminLogin} class="space-y-6">
        <!-- Username Field -->
        <div>
          <label for="admin-username" class="block text-sm font-semibold text-gray-700 mb-2 uppercase tracking-wide">
            Username
          </label>
          <div class="flex items-center border-2 border-gray-300 rounded-lg overflow-hidden focus-within:border-green-500 transition-colors">
            <div class="bg-teal-600 px-4 py-3.5 flex items-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
            </div>
            <input 
              id="admin-username"
              type="text" 
              bind:value={username}
              class="flex-1 px-3 py-3 text-gray-700 bg-gray-200 focus:outline-none focus:bg-white transition-colors border-0 m-0"
              placeholder="Admin"
              required
            />
          </div>
        </div>
        
        <!-- Password Field -->
        <div>
          <label for="admin-password" class="block text-sm font-semibold text-gray-700 mb-2 uppercase tracking-wide">
            Password
          </label>
          <div class="flex items-center border-2 border-gray-300 rounded-lg overflow-hidden focus-within:border-green-500 transition-colors">
            <div class="bg-teal-600 px-4 py-3.5 flex items-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
              </svg>
            </div>
            <input 
              id="admin-password"
              type="password" 
              bind:value={password}
              class="flex-1 px-3 py-3 text-gray-700 bg-gray-200 focus:outline-none focus:bg-white transition-colors border-0 m-0"
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
        <div class="flex gap-3 pt-2">
          <button
            type="button"
            class="flex-1 px-6 py-3 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition-colors font-semibold uppercase tracking-wide"
            on:click={closeModal}
          >
            Cancel
          </button>
          <button
            type="submit"
            class="flex-1 px-6 py-3 text-white bg-green-500 rounded-lg hover:bg-green-600 transition-colors font-semibold uppercase tracking-wide shadow-md hover:shadow-lg"
          >
            Login
          </button>
        </div>
      </form>
    </div>
  </div>
{/if}
