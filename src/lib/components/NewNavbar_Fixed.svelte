<script lang="ts">
  import SimpleAdminLogin from './SimpleAdminLogin.svelte';
  import ChangePasswordModal from './ChangePasswordModal.svelte';
  import { page } from '$app/stores';
  import { onMount, onDestroy } from 'svelte';
  import { browser } from '$app/environment';
  import { fade } from 'svelte/transition';
  
  let showModal = false;
  let showChangePasswordModal = false;
  let isAdminLoggedIn = false;
  let showProfileDropdown = false;
  let cookieCheckInterval: NodeJS.Timeout | null = null;

  function isActive(path: string): boolean {
    return $page.url.pathname === path;
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
      }
    }
  }

  // Start checking for cookie changes periodically
  function startCookieCheck() {
    if (browser && !cookieCheckInterval) {
      cookieCheckInterval = setInterval(checkAdminStatus, 500);
    }
  }

  // Stop checking for cookie changes
  function stopCookieCheck() {
    if (cookieCheckInterval) {
      clearInterval(cookieCheckInterval);
      cookieCheckInterval = null;
    }
  }

  // Profile dropdown functions
  function toggleProfileDropdown() {
    showProfileDropdown = !showProfileDropdown;
  }

  function closeProfileDropdown() {
    showProfileDropdown = false;
  }

  function logout() {
    // Clear authentication
    document.cookie = 'auth=; path=/; max-age=0'; // Clear auth cookie
    localStorage.removeItem('userData'); // Clear user data
    
    // Update state
    isAdminLoggedIn = false;
    showProfileDropdown = false;
    
    // Reload page to reflect changes
    window.location.reload();
  }

  function changePassword() {
    showChangePasswordModal = true;
    showProfileDropdown = false;
  }

  function handleUserIconClick() {
    if (isAdminLoggedIn) {
      toggleProfileDropdown();
    } else {
      showModal = true;
    }
  }

  // Close dropdown when clicking outside
  function handleDocumentClick(event: MouseEvent) {
    const target = event.target as HTMLElement;
    if (!target.closest('.profile-dropdown')) {
      showProfileDropdown = false;
    }
  }

  onMount(() => {
    checkAdminStatus();
    startCookieCheck();
    
    if (browser) {
      document.addEventListener('click', handleDocumentClick);
    }
  });

  onDestroy(() => {
    stopCookieCheck();
    
    if (browser) {
      document.removeEventListener('click', handleDocumentClick);
    }
  });
</script>

<nav class="navbar navbar-expand-lg navbar-light custom-nav">
  <div class="container-fluid">
    <!-- User Icon with Profile Dropdown -->
    <div class="navbar-icon-container profile-dropdown">
      <button class="navbar-icon-button" on:click={handleUserIconClick} aria-label="User menu">
        <img src="/user.png" alt="User Login" class="navbar-user-icon"/>
      </button>
      
      <!-- Profile Dropdown Menu -->
      {#if isAdminLoggedIn && showProfileDropdown}
        <div 
          class="profile-dropdown-menu"
          transition:fade={{ duration: 150 }}
        >
          <button
            on:click={changePassword}
            class="dropdown-item"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="dropdown-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1721 9z" />
            </svg>
            Change Password
          </button>
          <div class="dropdown-divider"></div>
          <button
            on:click={logout}
            class="dropdown-item logout-item"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="dropdown-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
            Log Out
          </button>
        </div>
      {/if}
    </div>
    
    <a 
      class="navbar-brand school-name"
      href="https://web.facebook.com/profile.php?id=100064275935073"
      target="_blank"
      rel="noopener noreferrer"
    >
      NEW CABALAN NATIONAL HIGH SCHOOL
    </a>

    <!-- Always show navigation buttons, remove Bootstrap collapse -->
    <div class="navbar-nav-container">
      <ul class="navbar-nav d-flex flex-row">
        <li class="nav-item">
          <a class="nav-link btn-header {isActive('/about') ? 'active' : ''}" href="/about">ABOUT</a>
        </li>
        <li class="nav-item">
          <a class="nav-link btn-header {isActive('/announcements') ? 'active' : ''}" href="/announcements">ANNOUNCEMENTS</a>
        </li>
        <li class="nav-item">
          <a class="nav-link btn-header {isActive('/calendar') ? 'active' : ''}" href="/calendar">CALENDAR</a>
        </li>
        <li class="nav-item">
          <a class="nav-link btn-header {isActive('/faculty') ? 'active' : ''}" href="/faculty">FACULTY</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<SimpleAdminLogin open={showModal} close={() => showModal = false} />
<ChangePasswordModal bind:show={showChangePasswordModal} />

<style>
  .navbar-icon-container {
    position: relative;
  }

  .navbar-icon-button {
    background: none;
    border: none;
    padding: 0;
    cursor: pointer;
  }

  .navbar-user-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    margin-right: 10px;
    transition: background-color 0.3s ease;
    transform: scale(1.1);
  }

  .navbar-user-icon:hover {
    background-color: #fccd41;
  }

  .profile-dropdown-menu {
    position: absolute;
    top: 100%;
    left: 0;
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    border: 1px solid #e5e7eb;
    min-width: 180px;
    z-index: 1050;
    margin-top: 5px;
    overflow: hidden;
  }

  .dropdown-item {
    width: 100%;
    padding: 12px 16px;
    border: none;
    background: none;
    text-align: left;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    color: #374151;
    transition: background-color 0.2s ease;
  }

  .dropdown-item:hover {
    background-color: #f3f4f6;
  }

  .logout-item {
    color: #dc2626;
  }

  .logout-item:hover {
    background-color: #fef2f2;
  }

  .dropdown-icon {
    width: 16px;
    height: 16px;
  }

  .dropdown-divider {
    height: 1px;
    background-color: #e5e7eb;
    margin: 4px 0;
  }

  .school-name {
    font-weight: bold;
    font-size: 1.2rem;
    font-family: 'Istok Web', sans-serif;
    color: #ffffff;
    margin-left: 40px;
    text-decoration: none;
  }

  .school-name:hover {
    color: #fccd41;
    text-decoration: none;
  }

  .custom-nav {
    background-color: #346357;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 1000;
    width: 100%;
    height: 70px; /* Set fixed height */
    display: flex;
    align-items: center;
  }

  .btn-header {
    background-color: #65D269;
    font-weight: bold;
    border-radius: 12px;
    padding: 6px 16px;
    margin: 10px 5px;
    margin-left: 16px;
    display: block;
    transition: background-color 0.5s ease;
    color: #000;
    text-decoration: none;
  }

  .btn-header:hover {
    background-color: #f1ca07;
    color: rgb(0, 0, 0);
    text-decoration: none;
  }

  .btn-header.active {
    background-color: #f1ca07;
    color: rgb(0, 0, 0);
  }

  .container-fluid {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.5rem 1rem;
  }

  .navbar-nav-container {
    display: flex;
    justify-content: flex-end;
    flex: 1;
  }

  .navbar-nav {
    display: flex;
    flex-direction: row;
    list-style: none;
    margin: 0;
    padding: 0;
    align-items: center;
  }

  .nav-item {
    margin: 0;
  }

  .nav-link {
    padding: 0;
  }

  /* Responsive design */
  @media (max-width: 768px) {
    .school-name {
      font-size: 1rem;
      margin-left: 20px;
    }

    .btn-header {
      padding: 4px 8px;
      margin-left: 4px;
      font-size: 0.8rem;
    }

    .navbar-user-icon {
      width: 35px;
      height: 35px;
    }

    .navbar-nav {
      flex-wrap: wrap;
    }
  }

  @media (max-width: 576px) {
    .school-name {
      font-size: 0.8rem;
      margin-left: 10px;
    }

    .btn-header {
      padding: 2px 6px;
      margin-left: 2px;
      font-size: 0.7rem;
    }

    .container-fluid {
      padding: 0.5rem;
      flex-wrap: wrap;
    }

    .navbar-nav-container {
      flex-basis: 100%;
      justify-content: center;
      margin-top: 0.5rem;
    }
  }
</style>
