<script>
    import AdminSignIn from '$lib/login.svelte';
    import {page} from '$app/stores'; // depraacated but still works in svelte kit 2/Import the page store to access the current path
    let showModal = false;
/**
 * @param {string} path
 * @returns {boolean}
 */
function isActive(path) {
  return $page.url.pathname === path;
}

</script>

<nav class = "navbar navbar-expand-lg navbar-light custom-nav">
    <div class ="container-fluid">
        <button class ="navbar-icon-button" on:click={() => showModal=true}>
                  <img src="/user.png" alt="userlogo" class="navbar-user-icon"/>
        </button>    
        <a 
            class ="navbar-brand school-name"
                 href ="https://web.facebook.com/profile.php?id=100064275935073"
                     target='_blank'
                       rel= "noopener noreferrer">
                    NEW CABALAN NATIONAL HIGH SCHOOL
                   </a>
       
            <div class ="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav d-flex flex-row">
                      <li class ="nav-item">
                         <a class="nav-link btn-header {isActive('/about') ? 'active' : ''}" href="/about">ABOUT</a>
                      </li>
                          <li class="nav-item">
                         <a class="nav-link btn-header {isActive('/announcement') ? 'active' : ''}" href="/announcement">ANNOUNCEMENT</a>
                          </li>

                             <li class="nav-item">
                               <a class ="nav-link btn-header {isActive('/calendar')? 'active' : ''}" href="/calendar">CALENDAR</a>
                             </li>
                                   <li class="nav-item">
                                       <a class ="nav-link btn-header {isActive('/faculty')?'active': ''}" href="/faculty">FACULTY</a>
                                   </li>
                    </ul> <!-- to stack vertically the about,announcement,then we use d-flex-row to put them horizontally -->
    </div>
</nav>
<AdminSignIn open={showModal} close={()=> showModal = false}/>
<style>
    .navbar-icon-button {
        background: none;
        border: none;
        padding: 0;
        cursor: pointer;
    }
    .navbar-user-icon {
        width: 40px; /* Adjust the size as needed */
        height: 40px; /* Adjust the size as needed */
        border-radius: 50%; /* Makes the icon circular */
        margin-right: 10px; /* Spacing between icon and text */
        transition: background-color 0.3s ease;
        transform: scale(1.1); /* Initial scale */
    }
    .navbar-user-icon:hover{
        background-color: #fccd41; /* Change background color on hover */
    }
    .school-name {
        font-weight: bold;
        font-size: 1.2rem;
        font-family: Istok Web, sans-serif;
        color: #ffffff; /* green color */
        margin-left: 40px; /* left margin for spacing */
        
    }
    .custom-nav {
        background-color: #346357;
        position:fixed;
        top:0;
        left: 0;
        right: 0;
        z-index: 1000; /* Ensure the navbar is above other content */
        width:100%;  
        
    }
    .btn-header{
        background-color: #65D269;
        font-weight: bold; /* bold the text */
        border-radius: 12px;
        padding: 6px 16px; /*the left px is the height */
       margin: 10px 5px; /* left px is the top and bottom margin (space) */
        margin-left:16px;
       display: block;
        transition: background-color 0.5s ease;
    }
    .btn-header:hover{
        background-color: #f1ca07;
        color:rgb(0, 0, 0)
    }
    .btn-header.active {
        background-color: #f1ca07; /* Highlight color for active link */
        color: rgb(0, 0, 0); /* Text color for active link */
    }
</style>