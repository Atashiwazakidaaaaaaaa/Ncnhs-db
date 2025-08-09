<script>
  import {goto} from '$app/navigation'; //for redirecting adfter login sucessful
  export let open = false;
  export let close = () => {};

function logout () {
  goto ("/about"); // Redirect to the about page
  close ();

}

function manageaccount() {
  goto ("/adminside/manageaaccount");
  close ();
}
</script>

{#if open}
  <div class="modal-backdrop"> 
 
    <nav class="signin-box">
      <img src="/ncnhs-logo-figma.png" alt="school-logo" class="school-logo" />
      <img src="/X-icon.png" on:click={close} alt="close-icon" class="close-icon" />
        
    
           <div class ="lower-buttons">
                <button class="btn btn-secondary" on:click={manageaccount}>Manage your account</button>
           </div>
           <div class="lower-buttons">
                   <button on:click={logout} class="btn btn-primary text-decoration-underline">LOGOUT</button>
                 
            </div>
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
    min-width: 200px;
    min-height: 450px;
    border: solid 4px #B1C6C7;

    box-shadow: 0 40px 8px rgba(0, 0, 0, 0.1);
  }

.lower-buttons {
    display: flex;
    justify-content: center;
    margin-top: 20px;
    gap: 1px;
    
  }
  .btn-secondary {
    background-color: #CCE1DD; 
    color: #0B6BD9; /* text color */
    font-weight: bold;
    border-radius: 25px;
    padding: 15px 50px; /* Adjust padding for better appearance */
  }
  .btn-primary {
    background-color: #346357; /* green color */
    color: white; /* text color */
    border-radius: 12px;
    padding: 10px 50px; /* Adjust padding for better appearance */
    margin-top: 20px;
  
  }
</style>
