
<script lang="ts">
  import { onMount } from 'svelte';
  import { fetchData } from "../../api.js";
  let content: { section_name: string; Info: string }[] = [];
  let loaded = false;
  let editingSection = '';
  let updatedContent = '';
  let isAdmin = false;
  onMount(async () => {
    content = await fetchData('get_about.php');
    // Check for admin cookie
    isAdmin = document.cookie.split('; ').some(row => row.startsWith('auth='));
    loaded = true;
  });
  function startEdit(section: string, info: string) {
    editingSection = section;
    updatedContent = info;
  }
  async function saveEdit() {
    const res = await fetch('http://localhost/back-ends/update_about.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ section_name: editingSection, Info: updatedContent })
    });
    const result = await res.json();
    if (result.success) {
      loaded = false;
      content = await fetchData('get_about.php');
      loaded = true;
      editingSection = '';
    } else {
      alert('Failed to save edit. ' + result.error);
    }
  }
  function getContent(section: string): string {
    const item = content.find(c => c.section_name.trim().toLowerCase() === section.trim().toLowerCase());
    return item ? item.Info : 'No content';
  }
</script>

{#if loaded}
  <div class="container text-center py-4">
    <img class="logo" src="/ncnhs-logo-figma.png" alt="NCNHS Logo" />

    <div class="row box-wrapper breakitdownyow d-flex justify-content-center"> 
      <div class="col-md-4">
        <div class="box{isAdmin ? ' admin' : ''}"> 
          <p class ="section-title">VISION</p>  
          <p>{getContent('vision')}</p>
          {#if editingSection === 'vision'}
            <textarea bind:value={updatedContent} rows="4" class="form-control"></textarea>
            <button on:click={saveEdit} class="btn btn-success mt-2">Save</button>
          {:else}
            <button on:click={() => startEdit('vision', getContent('vision'))} class="btn btn-primary mt-2">Edit</button>
          {/if}
        </div>  
      </div>

      <div class="col-md-4">
        <div class="box{isAdmin ? ' admin' : ''}">
          <p class ="section-title">Mission</p>
          <p>{getContent('mission')}</p>
          {#if editingSection === 'mission'}
            <textarea bind:value={updatedContent} rows="5" class="form-control"></textarea>
            <button on:click={saveEdit} class="btn btn-success mt-2">Save</button>
          {:else}
            <button on:click={() => startEdit('mission', getContent('mission'))} class="btn btn-primary mt-2">Edit</button>
          {/if}
        </div>
      </div>

      <div class="col-md-4">
        <div class="box{isAdmin ? ' admin' : ''}">
          <p class ="section-title">Core Values</p> 
          <p>{getContent('core_values')}</p>
          {#if editingSection ==='core_values'}
            <textarea bind:value={updatedContent} rows ="6" class="form-control"></textarea>
            <button on:click= {saveEdit} class ="btn btn-success mt-2">Save</button>
          {:else} 
            <button on:click ={()=> startEdit ('core_values', getContent ('core_values'))} class ="btn btn-primary mt-2"> Edit </button> 
          {/if}
        </div> 
      </div>
    </div>

  <div class="boxAbout mt-4{isAdmin ? ' admin' : ''}">
      <p>{getContent('ncnhs_about')}</p>
      {#if editingSection === 'ncnhs_about'}
        <textarea bind:value={updatedContent} rows="8" class="form-control"></textarea>
        <button on:click={saveEdit} class="btn btn-success mt-2">Save</button>
      {:else}
        <button on:click={() => startEdit('ncnhs_about', getContent('ncnhs_about'))} class="btn btn-primary mt-2">Edit</button>
      {/if}
    </div>
  </div>
{:else}
  <div class="text-center my-5">
    <div class="spinner-border text-info" role="status">
      <span class="visually-hidden">Loading...</span>
    </div>
    <p class="mt-2">Fetching content...</p>
  </div>
{/if}

    <style>
        .logo {
            width:200px; /* Adjust the size as needed */
            height: auto; /* Maintain aspect ratio */
            margin-bottom: 20px; /* Spacing below the logo */
            margin-top: -10px; /* Move logo up by 10px */
            margin-left: auto; /* Center the logo/ autos is for centering stuff*/
            margin-right: auto; /* Center the logo */
          
        }
    .box {
      background: white;
      border: 5px solid #74BEC1;
      border-radius: 20px;
      padding: 2rem;
      margin: 10px;
      min-height: 330px;
      width: 350px;
      align-items: center;
      margin: 10px auto;
      font-family: 'Nunito', sans-serif;
    }
    .box.admin {
      background: rgb(249, 247, 119);
    }
        .section-title {
            font-weight: bold;
            font-size: 1.5rem;
            color: #265073; /* hehe */
            margin-bottom: 1rem;
            font-family: 'Merriweather', serif;
        }
        
    .boxAbout {
      background: white;
      border: 5px solid #74BEC1;
      border-radius: 20px;
      padding: 2rem;
      margin: 10px;
      min-height: 350px;
      min-width: auto;
      margin: 10px auto;
      font-family: 'Nunito', sans-serif;
    }
    .boxAbout.admin {
      background: #9EFCB4;
    }
        .breakitdownyow{
          word-break: normal;
          overflow-wrap: anywhere;
        }
    </style>