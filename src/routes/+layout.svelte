<script lang="ts">
	import '../app.css';
	import NewNavbar from '$lib/components/NewNavbar.svelte';
	import { page } from '$app/stores';
	import { onMount, onDestroy } from 'svelte';
	import { loadUserFromLocalStorage } from '$lib/stores/user';
	import { browser } from '$app/environment';
	import 'bootstrap/dist/css/bootstrap.min.css';
	
	let { children } = $props();
	let isAdminLoggedIn = $state(false);
	let cookieCheckInterval: NodeJS.Timeout | null = null;

	// Function to check admin login status from cookies
	function checkAdminStatus() {
		if (browser) {
			const authCookie = document.cookie
				.split('; ')
				.find(row => row.startsWith('auth='));
			const newAdminStatus = !!authCookie;
			
			if (newAdminStatus !== isAdminLoggedIn) {
				isAdminLoggedIn = newAdminStatus;
				console.log('Layout - Admin status changed:', isAdminLoggedIn);
				console.log('Layout - Should show admin background:', isAdminLoggedIn);
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

	onMount(() => {
		loadUserFromLocalStorage();
		checkAdminStatus();
		startCookieCheck();
	});

	onDestroy(() => {
		stopCookieCheck();
	});
</script>

<NewNavbar />

<div class="page-background">
	<div class="page-background-top" style="background-image: url('{isAdminLoggedIn ? '/adminbackground.png' : '/nchs-background.JPG'}')"></div>
	<div class="page-background-bottom {isAdminLoggedIn ? 'admin-background-bottom' : ''}"></div>
</div>
<div class="content-wrapper">
	{@render children()}
</div>

<style>
	.page-background {
		position: fixed;
		top: 0;
		left: 0;
		width: 100%;
		height: 100vh;
		z-index: -1; /* Behind all content */
		pointer-events: none; /* Allows clicking through to content */
	}

	.page-background-top {
		position: absolute;
		top: 0;
		left: 0;
		width: 100%;
		height: 70vh; /* Fixed i want it 70/30 50% of viewport */
		background-size: cover;
		background-position: center;
		background-color: aliceblue;
		filter: blur(5px);
		transform: scale(1.1);
		transition: background-image 0.3s ease;
	}

	.page-background-bottom {
		position: absolute;
		bottom: 0;
		left: 0;
		width: 100%;
		height: 30vh; /* Fixed 50% of viewport */
		background-color: #0D627A;
		transition: background-color 0.3s ease;
	}

	/* Admin background bottom styling */
	.page-background-bottom.admin-background-bottom {
		background-color: #155674;
	}

	.content-wrapper {
		position: relative;
		z-index: 1; /* Above background */
		min-height: 100vh;
		padding: 2rem;
		padding-top: 80px; /* Account for navbar height */
	}
</style>
