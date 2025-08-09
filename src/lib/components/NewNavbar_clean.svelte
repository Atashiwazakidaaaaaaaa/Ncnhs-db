<script>
    import AraLogin from './AraLogin.svelte';
    import { page } from '$app/stores';
    
    let showModal = false;

    /**
     * @param {string} path
     * @returns {boolean}
     */
    function isActive(path) {
        return $page.url.pathname === path;
    }
</script>

<nav class="navbar navbar-expand-lg navbar-light custom-nav">
    <div class="container-fluid">
        <button class="navbar-icon-button" on:click={() => showModal = true}>
            <img src="/user.png" alt="userlogo" class="navbar-user-icon"/>
        </button>    
        
        <a 
            class="navbar-brand school-name"
            href="https://web.facebook.com/profile.php?id=100064275935073"
            target="_blank"
            rel="noopener noreferrer">
            NEW CABALAN NATIONAL HIGH SCHOOL
        </a>
       
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
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

<AraLogin open={showModal} close={() => showModal = false}/>

<style>
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
    
    .school-name {
        font-weight: bold;
        font-size: 1.2rem;
        font-family: 'Istok Web', sans-serif;
        color: #ffffff;
        margin-left: 40px;
        text-decoration: none;
    }
    
    .school-name:hover {
        color: #ffffff;
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
        color: #000000;
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
</style>
