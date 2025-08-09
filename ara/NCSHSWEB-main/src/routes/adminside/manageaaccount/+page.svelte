<script lang="ts">
  import { user, loadUserFromLocalStorage } from '$lib/stores/user';
  import { onMount } from 'svelte';
  import { goto } from '$app/navigation';

 // export let close = () => {};

  let username = '';
  let password = '';

  let showmodal = true;

   function close() {
    goto('/adminside/aboutadmin');
  }

  // Load user data into store
  onMount(() => {
    loadUserFromLocalStorage();

    const userData = localStorage.getItem('userData');
    if (!userData) {
      goto('/login');
    }

    // Fill form with existing user data
    user.subscribe(u => {
      if (u) {
        username = u.username;
      }
    });
  });

  async function updateAccount() {
    const formData = new FormData();
    formData.append("username", username);
    formData.append("password", password);

    const res = await fetch('http://localhost/back-ends/manageaccount.php', {
      method: 'POST',
      body: formData,
    credentials: 'include'
    });

    const data = await res.json();
    if (data.success) {
      alert("Account updated successfully");

      // Save the updated info in localStorage and store
      const updatedUser = { username }; // password usually not stored
      localStorage.setItem('userData', JSON.stringify(updatedUser));
      user.set(updatedUser);

      close();
      goto('/adminside/aboutadmin');  
    } else {
      alert("Error updating account: " + data.message);
    }
  }

  function cancel() {
      goto('/adminside/aboutadmin');
  }
</script>


{#if showmodal}
  <div class="modal-backdrop"> 
 
    <nav class="signin-box">
      <img src="/ncnhs-logo-figma.png" alt="school-logo" class="school-logo" />
      <img src="/X-icon.png" on:click={close} alt="close-icon" class="close-icon" />
           <b class="username">New Username</b>
    
    <form on:submit|preventDefault={updateAccount}>
         <div class="input-group mb-2">
            <span class="usericon">
                         <img src="/admin-user-icon.png" alt="user-icon-admin" class="iconuser" />
                   </span>
      
                         <input type="text" placeholder="Enter new username" class="form-control mb-2" bind:value= {username} />
                           </div>
    

  <!-- binding is importnat from the backe-end script -->
      <b class ="username">New Password</b>

          <div class="input-group mb-2">
            <span class="usericon">
                     <img src ="/admin-password-icon.png" alt="password-icon-admin" class="iconuser" />
               </span>
                      <input type="password" bind:value={password} placeholder="Enter new password" class="form-control mb-2" />
          </div>
                    
    
           <div class ="lower-buttons">
                <button class="btn btn-secondary" on:click={cancel}>CANCEL</button>

                   <button type="submit" class="btn btn-primary">SAVE</button>
                 
            </div>
    </form>
    </nav>
  
  </div>
  
  {/if}

<style>
  .modal-backdrop {
    position: fixed;
    inset: 0;
    background: #ffffff80; /* light white overlay */
    z-index: 500;
    
  }
  .close-icon {
    position: absolute;
    top: 20px;
    right: 20px;
    width: 30px; /* Adjust size as needed */
    height: 30px; /* Adjust size as needed */
    cursor: pointer;
  }
.school-logo {
    width: 120px; 
    height: auto; /* Maintain aspect ratio with width */
    display: block;
    margin: 0px auto 5px; /* top, centered, buttom */
    transform: translateY(-20px);
    
    
    
  }
  .signin-box {
    position: fixed;  
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);  
    background-color: #FFF2BE;
    padding: 5rem;
    border-radius: 27px;
    z-index: 1000;
    min-width: 550px;
    min-height: 450px;
    border: solid 4px #9eb1b2;

    box-shadow: 0 40px 8px rgba(0, 0, 0, 0.1);
  }
  .usericon {
    width: 50px; /* Adjust size as needed */
    height: 51px; /* Adjust size as needed */
    background-color: #096B68;
    border-top-left-radius: 12px;
    border-bottom-left-radius: 12px;
    border:#000000 solid 2px;
    margin-top: 10px;
    
  }
  .username {
    font-size: 1rem; /*1rem is 16px*/
    color: #FF1700; /* black color */
    text-align: center;
    margin:0 auto 10px

  }
  .form-control {
    border-radius: 12px;
    padding: 11.9px; /* Adjust padding for better appearance */
    border: solid 2px #000000; /* Border color */
    border-bottom: solid 2px #000000;
    background-color:#CCE1DD;
    margin-top: 10px;
    
  }
.lower-buttons {
    display: flex;
    justify-content: center;
    margin-top: 20px;
    gap: 1px;

  }
  .btn-secondary {
    background-color: #FF8080; 
    color: white; /* text color */
    border-radius: 12px;
    padding: 10px 50px; /* Adjust padding for better appearance */
  }
  .btn-primary {
    background-color: #5CB338; /* green color */
    color: white; /* text color */
    border-radius: 12px;
    padding: 10px 55px; /* Adjust padding for better appearance */
  }
</style>
