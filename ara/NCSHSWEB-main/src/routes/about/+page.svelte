<script lang="ts">
  import { onMount } from 'svelte';
  import { fetchData} from "../../api";

  // Make sure TS knows the type of content
  let content: { section_name: string; Info: string }[] = [];
  //
  let loaded= false;

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


 function getContent(section: string): string {
    const item = content.find(c => c.section_name.trim().toLowerCase() === section.trim().toLowerCase());
    console.log(`Content for ${section}:`, item);
    return item ? item.Info : 'No content';
  }
</script>


{#if loaded}
  <div class="container text-center py-5">
    <img class="logo" src="/ncnhs-logo-figma.png" alt="NCNHS Logo" />

    <div class="row box-wrapper breakitdownyow d-flex justify-content-center"> 
      <div class="col-md-4">
        <div class="box"> 
          <p class ="section-title">VISION</p>  
          <p>{getContent('vision')}</p>
        </div>  
      </div>

      <div class="col-md-4">
        <div class="box">
          <p class ="section-title">Mission</p>
          <p>{getContent('mission')}</p>
        </div>
      </div>

      <div class="col-md-4">
        <div class="box">
          <p class ="section-title">Core Values</p> 
          <p>{getContent('core_values')}</p>
        </div>
      </div>
    </div>

    <div class="boxAbout mt-4">
      <p>{getContent('ncnhs_about')}</p>
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
            border: 5px solid #74BEC1; /* Light border */
            border-radius: 20px;
            padding: 2rem;
            margin: 10px;
            min-height: 330px;
            width: 350px;
            align-items: center;
            margin: 10px auto; /* Center the box */
            font-family: 'Nunito', sans-serif;
            
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
            border: 5px solid #74BEC1; /* Light border */
            border-radius: 20px;
            padding: 2rem;
            margin: 10px;
            min-height: 350px;
            min-width: auto;
            margin: 10px auto; /* Center the box */
            font-family: 'Nunito', sans-serif;
        }
        .breakitdownyow{
          word-break: normal;
          overflow-wrap: anywhere;
        }
    </style>