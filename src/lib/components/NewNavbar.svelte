<script lang="ts">
  import SimpleAdminLogin from './SimpleAdminLogin.svelte';
  import ChangePasswordModal from './ChangePasswordModal.svelte';
  import { page } from '$app/stores';
  import { onMount, onDestroy } from 'svelte';
  import { browser } from '$app/environment';
  import { fade, slide } from 'svelte/transition';
  
  let showModal = $state(false);
  let isAdminLoggedIn = $state(false);
  let showProfileDropdown = $state(false);
  let showMobileMenu = $state(false);
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

  function toggleMobileMenu() {
    showMobileMenu = !showMobileMenu;
  }

  function closeMobileMenu() {
    showMobileMenu = false;
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
    if (!target.closest('.mobile-menu-toggle') && !target.closest('.mobile-nav')) {
      showMobileMenu = false;
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

<nav class="navbar custom-nav {isAdminLoggedIn ? 'admin-nav' : ''}">
  <div class="container-fluid">
    <div class="navbar-left-section">
      <!-- User Icon with Profile Dropdown -->
      <div class="navbar-icon-container profile-dropdown">
        <button class="navbar-icon-button" onclick={handleUserIconClick} aria-label="User menu">
          <img src="/user.png" alt="User Login" class="navbar-user-icon"/>
        </button>
        
        <!-- Profile Dropdown Menu -->
        {#if isAdminLoggedIn && showProfileDropdown}
          <div
            class="profile-dropdown-menu"
            transition:fade={{ duration: 150 }}
          >
            <button
              onclick={logout}
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
        <span class="school-name-full">NEW CABALAN NATIONAL HIGH SCHOOL</span>
        <span class="school-name-short">NCNHS</span>
      </a>
    </div>

    <!-- Mobile Menu Toggle Button -->
    <button class="mobile-menu-toggle" onclick={toggleMobileMenu} aria-label="Toggle menu">
      <div class="hamburger-line {showMobileMenu ? 'active' : ''}"></div>
      <div class="hamburger-line {showMobileMenu ? 'active' : ''}"></div>
      <div class="hamburger-line {showMobileMenu ? 'active' : ''}"></div>
    </button>

    <!-- Desktop Navigation -->
    <div class="navbar-nav-container desktop-nav">
      <ul class="navbar-nav d-flex flex-row">
        <li class="nav-item">
          <a class="nav-link btn-header {isAdminLoggedIn ? 'admin-btn-header' : ''} {isActive('/about') ? 'active' : ''}" href="/about">ABOUT</a>
        </li>
        <li class="nav-item">
          <a class="nav-link btn-header {isAdminLoggedIn ? 'admin-btn-header' : ''} {isActive('/announcements') ? 'active' : ''}" href="/announcements">ANNOUNCEMENTS</a>
        </li>
        <li class="nav-item">
          <a class="nav-link btn-header {isAdminLoggedIn ? 'admin-btn-header' : ''} {isActive('/calendar') ? 'active' : ''}" href="/calendar">CALENDAR</a>
        </li>
        <li class="nav-item">
          <a class="nav-link btn-header {isAdminLoggedIn ? 'admin-btn-header' : ''} {isActive('/faculty') ? 'active' : ''}" href="/faculty">FACULTY</a>
        </li>
      </ul>
    </div>
  </div>

  <!-- Mobile Navigation Menu -->
  {#if showMobileMenu}
    <div class="mobile-nav" transition:slide={{ duration: 300 }}>
      <div class="mobile-nav-content">
        <a 
          class="mobile-nav-link {isActive('/about') ? 'active' : ''}" 
          href="/about"
          onclick={closeMobileMenu}
        >
          <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          About
        </a>
        <a 
          class="mobile-nav-link {isActive('/announcements') ? 'active' : ''}" 
          href="/announcements"
          onclick={closeMobileMenu}
        >
          <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
          </svg>
          Announcements
        </a>
        <a 
          class="mobile-nav-link {isActive('/calendar') ? 'active' : ''}" 
          href="/calendar"
          onclick={closeMobileMenu}
        >
          <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
          </svg>
          Calendar
        </a>
        <a 
          class="mobile-nav-link {isActive('/faculty') ? 'active' : ''}" 
          href="/faculty"
          onclick={closeMobileMenu}
        >
          <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
          </svg>
          Faculty
        </a>
      </div>
    </div>
  {/if}
</nav>

<SimpleAdminLogin open={showModal} close={() => showModal = false} />

<style>
  .navbar-left-section {
    display: flex;
    align-items: center;
    gap: 15px;
  }

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
    margin-left: 20px;
    text-decoration: none;
    text-align: left;
  }

  .school-name:hover {
    color: #fccd41;
    text-decoration: none;
  }

  .school-name-short {
    display: none;
  }

  .school-name-full {
    display: inline;
  }

  .mobile-menu-toggle {
    display: none;
    flex-direction: column;
    background: none;
    border: none;
    cursor: pointer;
    padding: 8px;
    z-index: 1001;
  }

  .hamburger-line {
    width: 25px;
    height: 3px;
    background-color: #ffffff;
    margin: 3px 0;
    transition: 0.3s;
    border-radius: 2px;
  }

  .hamburger-line.active:nth-child(1) {
    transform: rotate(-45deg) translate(-6px, 6px);
  }

  .hamburger-line.active:nth-child(2) {
    opacity: 0;
  }

  .hamburger-line.active:nth-child(3) {
    transform: rotate(45deg) translate(-6px, -6px);
  }

  .mobile-nav {
    position: fixed;
    top: 70px;
    left: 0;
    right: 0;
    background-color: inherit;
    z-index: 999;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
  }

  .mobile-nav-content {
    padding: 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
  }

  .mobile-nav-link {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    color: #ffffff;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.3s ease;
  }

  .mobile-nav-link:hover {
    background-color: rgba(255, 255, 255, 0.1);
    color: #fccd41;
    text-decoration: none;
  }

  .mobile-nav-link.active {
    background-color: #fccd41;
    color: #000000;
  }

  .nav-icon {
    width: 20px;
    height: 20px;
  }

  .desktop-nav {
    display: flex;
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
    transition: background-color 0.3s ease;
  }

  /* Admin header styling - dark blue/purple background */
  .custom-nav.admin-nav {
    background-color: #110133;
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

  /* Admin button styling - lighter green */
  .btn-header.admin-btn-header {
    background-color: #9FFF97;
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
    .desktop-nav {
      display: none;
    }

    .mobile-menu-toggle {
      display: flex;
    }

    .school-name {
      font-size: 1rem;
      margin-left: 0;
    }

    .school-name-full {
      display: none;
    }

    .school-name-short {
      display: inline;
    }

    .navbar-user-icon {
      width: 35px;
      height: 35px;
    }

    .container-fluid {
      padding: 0.5rem 1rem;
    }

    .profile-dropdown-menu {
      left: auto;
      right: 0;
    }
  }

  @media (max-width: 576px) {
    .school-name {
      font-size: 0.9rem;
    }

    .navbar-user-icon {
      width: 32px;
      height: 32px;
    }

    .container-fluid {
      padding: 0.5rem;
    }

    .mobile-nav-content {
      padding: 0.5rem;
    }

    .mobile-nav-link {
      padding: 10px 12px;
      font-size: 0.9rem;
    }
  }

  @media (min-width: 769px) {
    .mobile-menu-toggle {
      display: none;
    }

    .mobile-nav {
      display: none;
    }
  }
</style>
