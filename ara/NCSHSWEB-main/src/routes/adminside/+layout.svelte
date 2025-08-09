<script lang="ts">
  import 'bootstrap/dist/css/bootstrap.min.css';
  import Navbar from '$lib/adminheader.svelte';
  import { goto } from '$app/navigation';
  import { onMount } from 'svelte';
  import { user, loadUserFromLocalStorage } from '$lib/stores/user';

  
</script>


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
    background-image: url('/adminbackground.png');
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