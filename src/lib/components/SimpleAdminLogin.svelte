<script lang="ts">
  import { goto } from '$app/navigation';
  import { user } from '$lib/stores/user';
  
  export let open = false;
  export let close = () => {};

  let username = "";
  let password = "";

  // Updated to use correct MAMP backend URL
  async function login() {
    const formLogin = new FormData();
    formLogin.append("username", username);
    formLogin.append("password", password);

    try {
      const res = await fetch('http://localhost/back-ends/login.php', {
        method: 'POST',
        body: formLogin,
        mode: 'cors'
      });

      if (!res.ok) {
        throw new Error(`HTTP error! status: ${res.status}`);
      }

      const data = await res.json();

      if (data.success) {
        // Save to localStorage and store
        const userData = { username };
        localStorage.setItem('userData', JSON.stringify(userData));
        user.set(userData);

        // Set auth cookie for cross-project compatibility
        document.cookie = 'auth=true; path=/; max-age=86400'; // 24 hours

        alert('Login successful!');
        close();
        
        // Trigger a page reload to update admin status
        window.location.reload();
      } else {
        alert(data.message || 'Login failed');
      }
    } catch (error) {
      console.error('Login error:', error);
      alert('Connection error. Please check if MAMP is running.');
    }
  }
</script>

{#if open}
  <div class="modal-backdrop" 
       on:click={close} 
       on:keydown={(e) => e.key === 'Escape' && close()}
       role="dialog" 
       aria-modal="true" 
       aria-labelledby="login-title"
       tabindex="-1">
    <div class="signin-box" 
         on:click|stopPropagation 
         on:keydown={(e) => e.key === 'Escape' && close()}
         role="document">
      <img src="/ncnhs-logo-figma.png" alt="NCNHS Logo" class="school-logo" />
      <button class="close-btn" on:click={close} aria-label="Close login modal">
        <img src="/X-icon.png" alt="Close" class="close-icon" />
      </button>
      
      <h2 id="login-title" class="visually-hidden">Admin Login</h2>
      
      <form on:submit|preventDefault={login}>
        <b class="username">USERNAME</b>
        <div class="input-group mb-2">
          <span class="usericon">
            <img src="/admin-user-icon.png" alt="User icon" class="iconuser" />
          </span>
          <input 
            type="text" 
            placeholder="Username" 
            class="form-control mb-2" 
            bind:value={username}
            required
            aria-label="Username"
          />
        </div>

        <b class="username">PASSWORD</b>
        <div class="input-group mb-2">
          <span class="usericon">
            <img src="/admin-password-icon.png" alt="Password icon" class="iconuser" />
          </span>
          <input 
            type="password" 
            bind:value={password} 
            placeholder="Password" 
            class="form-control mb-2"
            required
            aria-label="Password"
          />
        </div>

        <div class="lower-buttons">
          <button type="button" class="btn btn-secondary" on:click={close}>CANCEL</button>
          <button type="submit" class="btn btn-primary">LOGIN</button>
        </div>
      </form>
    </div>
  </div>
{/if}

<style>
  .modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(255, 255, 255, 0.8);
    z-index: 500;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .close-btn {
    position: absolute;
    top: 20px;
    right: 20px;
    background: none;
    border: none;
    cursor: pointer;
    padding: 0;
  }

  .close-icon {
    width: 30px;
    height: 30px;
  }

  .school-logo {
    width: 150px;
    height: auto;
    display: block;
    margin: 0px auto 20px;
    transform: translateY(-20px);
  }

  .signin-box {
    background-color: #ffffff;
    padding: 3rem;
    border-radius: 27px;
    z-index: 1000;
    min-width: 550px;
    min-height: 475px;
    border: solid 4px #9eb1b2;
    box-shadow: 0 40px 8px rgba(0, 0, 0, 0.1);
    position: relative;
  }

  .usericon {
    width: 50px;
    height: 51px;
    background-color: #096B68;
    border-top-left-radius: 12px;
    border-bottom-left-radius: 12px;
    border: #000000 solid 2px;
    margin-top: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .iconuser {
    width: 24px;
    height: 24px;
  }

  .username {
    font-size: 1rem;
    font-weight: bold;
    text-align: center;
    margin: 0 auto 10px;
    display: block;
  }

  .input-group {
    display: flex;
    align-items: center;
    margin-bottom: 1rem;
  }

  .form-control {
    border-radius: 12px;
    padding: 11.9px;
    border: solid 2px #000000;
    background-color: #CCE1DD;
    margin-top: 10px;
    flex: 1;
    border-left: none;
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
  }

  .lower-buttons {
    display: flex;
    justify-content: center;
    margin-top: 20px;
    gap: 10px;
  }

  .btn {
    border-radius: 12px;
    padding: 10px 50px;
    border: none;
    cursor: pointer;
    font-weight: bold;
  }

  .btn-secondary {
    background-color: #CCE1DD;
    color: #000;
  }

  .btn-secondary:hover {
    background-color: #b8d4cf;
  }

  .btn-primary {
    background-color: #62C666;
    color: white;
  }

  .btn-primary:hover {
    background-color: #4ea052;
  }

  .visually-hidden {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
  }
</style>
