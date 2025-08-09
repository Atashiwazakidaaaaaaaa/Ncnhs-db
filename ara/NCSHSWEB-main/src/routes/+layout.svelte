 <script lang="ts">
  import { onMount } from 'svelte';
  import { user, loadUserFromLocalStorage } from '$lib/stores/user';
  import Navbar from '$lib/Navbar.svelte';
  import { page } from '$app/stores';
  import { derived } from 'svelte/store';
  import AdminHeader from '$lib/adminheader.svelte';
  import 'bootstrap/dist/css/bootstrap.min.css';

  onMount(() => {
    loadUserFromLocalStorage(); //  This avoids repeating localStorage logic
  });

   // Create a reactive store to check if path includes "/admin"
  const isAdminPage = derived(page, $page => $page.url.pathname.startsWith('/admin'));

</script>

{#if $isAdminPage}
  <AdminHeader />
{:else}
  <Navbar />
{/if}
<div class="page-background">
    <div class="page-background-top"></div>
    <div class="page-background-bottom"></div>
</div>
<div class="content-wrapper">
    <slot/> 
</div>

<footer/>

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
    background-image: url('/nchs-background.JPG');
    background-size: cover;
    background-position: center;
    background-color: aliceblue;
    filter: blur(5px);
    transform: scale(1.1);
  }

  .page-background-bottom {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 30vh; /* Fixed 50% of viewport */
    background-color: #0D627A;
  }

  .content-wrapper {
    position: relative;
    z-index: 1; /* Above background */
    min-height: 100vh;
    padding: 2rem;
    padding-top: 80px; /* Account for navbar height */
  }
</style>