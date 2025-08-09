<script lang="ts">
  import { goto } from '$app/navigation'; // for redirecting after login
  import { user } from '$lib/stores/user'; // 🆕 import the user store
  export let open = false;
  export let close = () => {};

  let username = "";
  let password = "";

  async function login () {
    const formLogin = new FormData();
    formLogin.append("username", username);
    formLogin.append("password", password);

    const res = await fetch('http://localhost/back-ends/login.php', {
      method: 'POST',
      body: formLogin,
     credentials: 'include'

    });

    const data = await res.json();

    if (data.success) {
      // Save to localStorage and store
      const userData = { username }; // You can add email if backend gives it
      localStorage.setItem('userData', JSON.stringify(userData));
      user.set(userData);

      goto("/admin");
      close();
    } else {
      alert(data.message);
    }
  }
</script>


{#if open}
  <div class="modal-backdrop"> 
 
    <nav class="signin-box">
      <img src="/ncnhs-logo-figma.png" alt="school-logo" class="school-logo" />
      <img src="/X-icon.png" on:click={close} alt="close-icon" class="close-icon" />
           <b class="username">USERNAME</b>
    
    <form on:submit|preventDefault={login}>
         <div class="input-group mb-2">
            <span class="usericon">
                         <img src="/admin-user-icon.png" alt="user-icon-admin" class="iconuser" />
                   </span>
      
                         <input type="text" placeholder="Username" class="form-control mb-2" bind:value= {username} />
                           </div>
    

  <!-- binding is importnat from the backe-end script -->
      <b class ="username">PASSWORD</b>
    
          <div class="input-group mb-2">
            <span class="usericon">
                     <img src ="/admin-password-icon.png" alt="password-icon-admin" class="iconuser" />
               </span>
                      <input type="password" bind:value={password} placeholder="Password" class="form-control mb-2" />
          </div>
                    
    
           <div class ="lower-buttons">
                <button class="btn btn-secondary" on:click={close}>CANCEL</button>
                
                   <button type="submit" class="btn btn-primary">LOGIN</button>
                 
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
    background-color: #ffffff;
    padding: 5rem;
    border-radius: 27px;
    z-index: 1000;
    min-width: 550px;
    min-height: 475px;
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
    font-weight: bold;
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
    background-color: #CCE1DD; /* yellow color */
    color: white; /* text color */
    border-radius: 12px;
    padding: 10px 50px; /* Adjust padding for better appearance */
  }
  .btn-primary {
    background-color: #62C666; /* green color */
    color: white; /* text color */
    border-radius: 12px;
    padding: 10px 50px; /* Adjust padding for better appearance */
  }
</style>
