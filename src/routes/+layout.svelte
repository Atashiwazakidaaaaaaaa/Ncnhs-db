<script lang="ts">
	import '../app.css';
	import Navbar from '$lib/components/navbar.svelte';
	import { page } from '$app/stores';
	
	let { children } = $props();
	
	// Map URL paths to navbar page names
	function getPageName(pathname: string): string {
		switch (pathname) {
			case '/about':
				return 'ABOUT';
			case '/announcements':
				return 'ANNOUNCEMENTS';
			case '/calendar':
				return 'CALENDAR';
			case '/faculty':
				return 'FACULTY';
			default:
				return 'HOME'; // Default fallback
		}
	}
	
	const currentPage = $derived(getPageName($page.url.pathname));
</script>

<Navbar currentPage={currentPage} />
{@render children()}
