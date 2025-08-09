<script lang="ts">
  import { onMount } from 'svelte';
  import { fetchData} from "../../../api";

  
  // Make sure TS knows the type of content
  let content:   { section_name: string; Info: string }[] = [];
  //
  let loaded= false;

  //edit logic 
  let editingSection= '';
  let updatedContent = '';                      


    /*existing data from db
    onMount(async () => {
  try {
    content = await fetchData('get_about.php');
    console.log("Fetched content:", content);


    //checker whats inside 
    content.forEach(item => {
      console.log('section_name', item.section_name);
    }); 
    loaded =true;
  } catch (error) {
    console.error("Failed to fetch content:", error);
  }
});
*/
  onMount(async () => { 
    content = await fetchData('get_about.php');
    console.log("Fetched content:", content);
    loaded = true; // Set loaded to true after fetching content
  });

  //function logic to start editing

  function startEdit (section:string,info:string) {

    editingSection = section;
    updatedContent = info;
  }
  
  //function logic to save the edited content/ needs an asynch, search what is asynch

async function saveEdit() {
  try {
    const res = await fetch('http://localhost/back-ends/update_about.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        section_name: editingSection,
        Info: updatedContent
      })
    });

    const result = await res.json();
    console.log('Response from PHP:', result); // Debug output

    if (result.success) {
      //refretch the contentr after saving so no need to manually refresh it
      loaded = false; // Set loaded to false to show loading state
      content = await fetchData('get_about.php');
      loaded =true;
      editingSection = ''; // Clear the editing section
      
  
    } else {
      alert('Failed to save edit. ' + result.error);
      console.error('Save error:', result.error);
    }
  } catch (err) {
    console.error('Fetch error:', err);
    alert('An error occurred while saving.');
  }
}


function getContent(section: string): string {
  const item = content.find(c => c.section_name.trim().toLowerCase() === section.trim().toLowerCase());
  console.log(`Content for ${section}:`, item);
  return item ? item.Info : 'No content';
}
</script>

{#if loaded}
  <div class="container text-center py-5">
    <img class="logo" src="/ncnhs-logo-figma.png" alt="NCNHS Logo" />

    <div class="row box-wrapper breakwords d-flex justify-content-center"> 
      <div class="col-md-4">
        <div class="box"> 
          <p class="section-title">Vision</p>  
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
        <div class="box">
          <p class ="section-title">Mission</p>
          <p>{getContent('mission')}</p>
          {#if editingSection ==='mission'} 
          <textarea bind:value={updatedContent} rows ="5" class ="form-control"></textarea>
          <button on:click={saveEdit} class= "btn btn-success mt-2">Save</button>
          {:else}
             <button on:click = {() =>startEdit ('mission', getContent ('mission'))} class="btn btn-primary mt-2">Edit</button>
             {/if}
        </div>
      </div>

      <div class="col-md-4">
        <div class="box">
          <p class ="section-title">Core Values</p> 
          <p>{getContent('core_values')}</p>
          {#if editingSection ==='core_values'}
          <textarea bind:value={updatedContent} rows ="6" class="form-control"></textarea>
          <button on:click= {saveEdit} class ="btn btn-success mt-2">Save</button>
          {:else} 
          <!-- class btn primary previously has a space so we just add hypen so it will be called in classes -->
          <button on:click ={()=> startEdit ('core_values', getContent ('core_values'))} class ="btn btn-primary mt-2"> Edit </button> 
          {/if}
        </div> 
      </div>
    </div>

    <div class="boxAbout mt-4">
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
            margin-top: -100px; /* Move logo up by some px */
            margin-left: auto; /* Center the logo/ autos is for centering stuff*/
            margin-right: auto; /* Center the logo */
          
        }
        .box {
            background: rgb(249, 247, 119);
            border: 5px solid #74BEC1; /* Light border */
            border-radius: 20px;
            padding: 2rem;
            margin: 10px;
            min-height: 330px;
            width: 350px;
            align-items: center;
            margin: 10px auto; /* Center the box */
        }
        .section-title {
            font-weight: bold;
            font-size: 1.5rem;
            color: #0E2954; /* hehe */
            margin-bottom: 1rem;
            font-family: 'Times New Roman', Times, serif;
        }
        .boxAbout {
            background: #9EFCB4;
            border: 5px solid #74BEC1; /* Light border */
            border-radius: 20px;
            padding: 2rem;
            margin: 10px;
            min-height: 350px;
            min-width: auto;
            margin: 10px auto; /* Center the box */
        }
        .btn-primary {
            background-color: #239BA7; /* Bootstraphas built in color but we will ovveride*/
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }
        .breakwords {
          word-break: normal;
          overflow-wrap:anywhere;
        }
    </style>