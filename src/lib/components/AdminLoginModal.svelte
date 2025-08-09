<script lang="ts">
  import { goto } from '$app/navigation';
  import { user } from '$lib/stores/user';
  
  export let open = false;
  export let close = () => {};

  let username = "";
  let password = "";

  async function login() {
    const formLogin = new FormData();
    formLogin.append("username", username);
    formLogin.append("password", password);

    try {
      const res = await fetch('http://localhost/back-ends/login.php', {
        method: 'POST',
        body: formLogin,
        credentials: 'include'
      });

      const data = await res.json();

      if (data.success) {
        // Save to localStorage and store
        const userData = { username };
        localStorage.setItem('userData', JSON.stringify(userData));
        user.set(userData);

        // Set auth cookie for cross-project compatibility
        document.cookie = 'auth=true; path=/; max-age=86400'; // 24 hours

        goto("/admin");
        close();
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
  <div class="modal-backdrop" on:click={close} role="dialog" aria-modal="true" aria-labelledby="login-title">
    <div class="signin-box" on:click|stopPropagation role="document">
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
    background: rgba(0, 0, 0, 0.5);
    z-index: 500;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .signin-box {
    background-color: #ffffff;
    padding: 2rem;
    border-radius: 15px;
    z-index: 1000;
    min-width: 400px;
    max-width: 90vw;
    border: solid 2px #9eb1b2;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    position: relative;
  }

  .school-logo {
    width: 100px;
    height: auto;
    display: block;
    margin: 0 auto 1rem;
  }

  .close-btn {
    position: absolute;
    top: 10px;
    right: 10px;
    background: none;
    border: none;
    cursor: pointer;
    padding: 5px;
  }

  .close-icon {
    width: 20px;
    height: 20px;
  }

  .username {
    font-size: 0.9rem;
    font-weight: bold;
    display: block;
    text-align: center;
    margin-bottom: 0.5rem;
  }

  .input-group {
    display: flex;
    margin-bottom: 1rem;
  }

  .usericon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 45px;
    background-color: #096B68;
    border-top-left-radius: 8px;
    border-bottom-left-radius: 8px;
    border: 1px solid #000000;
    border-right: none;
  }

  .iconuser {
    width: 24px;
    height: 24px;
  }

  .form-control {
    flex-grow: 1;
    border-radius: 0 8px 8px 0;
    padding: 10px;
    border: 1px solid #000000;
    background-color: #CCE1DD;
  }

  .lower-buttons {
    display: flex;
    justify-content: center;
    margin-top: 1.5rem;
    gap: 1rem;
  }

  .btn {
    border-radius: 8px;
    padding: 10px 20px;
    border: none;
    cursor: pointer;
    font-weight: bold;
  }

  .btn-secondary {
    background-color: #f0ad4e;
    color: white;
  }

  .btn-primary {
    background-color: #5cb85c;
    color: white;
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
