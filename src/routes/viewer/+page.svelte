<script lang="ts">
    import { onMount } from 'svelte';
    import { page } from '$app/stores'; 
    import Navbar from '$lib/components/navbar.svelte';
    interface Announcement {
   
    	id: string;
    	title: string;
    	date: string;
    	content: string; // Use @html, so this can contain HTML
    }
    const API_URL = 'http://localhost/PHP/api/api.php';

    let announcements: Announcement[] = [
        {
            id: 'announcement-1',
            title: 'Parent-Teacher Conference Schedule',
            date: 'August 5th 2025',
            content: `
                <p>Dear Parents and Guardians,
                </p>
                <p>Our annual Parent-Teacher Conference is scheduled for <strong>August 5th, 2025</strong>, from 8:00 AM to 4:00 PM. This is a crucial opportunity to discuss your child's academic progress and overall well-being.
                </p>
                <p>Please ensure you have booked your slots with your child's respective teachers through our online booking system. Specific schedules and guidelines have been sent to your registered emails. You can also download the complete schedule document below.
                </p>
                <p>We look forward to seeing you there!
                </p>
            `
        },
        {
            id: 'announcement-2',
            title: 'Welcome Back to School!',
            date: 'July 1, 2025',
            content: `
                <p>Welcome back, students and faculty, to another exciting academic year at New Cabalan National High School! We are thrilled to have you all back on campus.</p>
                <p>Please take note of the important dates and events for the first quarter, including orientation sessions and club registration deadlines. These are outlined in the attached welcome guide.</p>
                <p>Remember to adhere to all school policies and health guidelines. Let's make this a productive and memorable year!</p>
            `
        },
        {
            id: 'announcement-3',
            title: 'AYOOOO!',
            date: 'July 1, 2025',
            content: `
                <p>Welcome back, students and faculty, to another exciting academic year at New Cabalan National High School! We are thrilled to have you all back on campus.</p>
                <p>Please take note of the important dates and events for the first quarter, including orientation sessions and club registration deadlines. These are outlined in the attached welcome guide.</p>
                <p>Remember to adhere to all school policies and health guidelines. Let's make this a productive and memorable year!</p>
            `
        }
    ];

    let selectedAnnouncementId: string | undefined;
	let isLoading = true;
	let errorMessage: string | null = null;
    

    onMount(async () => {
		try {
			const response = await fetch(API_URL);

			if (!response.ok) {
				const errorText = await response.text();
				console.error('API Response Error:', errorText);
				throw new Error(`Failed to fetch announcements. Server responded with status: ${response.status}`);
			}

			announcements = await response.json();

			if (announcements.length > 0) {
				selectedAnnouncementId = announcements[0].id;
			}
		} catch (error) {
			console.error('Error fetching announcements:', error);
			if (error instanceof SyntaxError) {
				errorMessage = 'Failed to parse server response. The API URL might be incorrect, leading to a "Not Found" page instead of JSON data.';
			} else {
				errorMessage = 'Could not load announcements. Please check the browser console for more details and ensure the backend server is running.';
			}
		} finally {
			isLoading = false;
		}
	});

    $: currentAnnouncement = announcements.find(
        (ann) => ann.id === selectedAnnouncementId
    );
    $: currentIndex = announcements.findIndex(
        (ann) => ann.id === selectedAnnouncementId
    );

    $: hasPrevious = currentIndex > 0;
    $: hasNext = currentIndex < announcements.length - 1;

    function goToPrevious() {
        if (hasPrevious) {
            selectedAnnouncementId = announcements[currentIndex - 1].id;
        }
    }

    function goToNext() {
        if (hasNext) {
            selectedAnnouncementId = announcements[currentIndex + 1].id;
        }
    }
</script>

<div class="page-container">
    <Navbar />
    <main style="background-image: url('/ncnhs.jpg');">
    <img src="/logo.png" alt="School Logo" class="logo" />
        <style>
            .logo {
                width: 200px;
                height: auto;
                margin-bottom: 20px;
                margin-top: -10px;
                margin-left: auto;
                margin-right: auto;
                display: block;
            }
        </style>
        <div class="title-container">
            <!-- Global Announcement Title: Montserrat ExtraBold, Centered -->
            <h3 class="announcement-title font-serif font-extrabold">ANNOUNCEMENT</h3>
        </div>
        
        {#if currentAnnouncement}
            <div class="announcement-container">
                <div class="announcement-box">
                    <!-- Dynamic Announcement Title: Montserrat Bold, Centered -->
                    <h2 class="font-serif font-bold text-center">{currentAnnouncement.title}</h2>
                    
                    <!-- Published Date: Montserrat Regular, Centered -->
                    <p class="announcement-date font-sans text-center font-bold">
                        Published: {currentAnnouncement.date}
                    </p>
                    
                    <!-- Content: Montserrat Regular, Centered (via CSS override below) -->
                    <!-- The <strong> tags inside will naturally be Montserrat Bold -->
                    <div class="announcement-content font-sans">
                        {@html currentAnnouncement.content}
                    </div>
                   </div>
                <div class="announcement-navigation">
                    <button on:click={goToPrevious} disabled={!hasPrevious}>
                        {'<'}
                    </button>
                    <button on:click={goToNext} disabled={!hasNext}>
                        {'>'}
                    </button>
                </div>
            </div>
        {:else}
            <p class="no-announcement-message">No announcements available.</p>
        {/if}

    </main>
</div>
<footer class="bg-green-900/80 text-white py-3 md:py-4 backdrop-blur-md border-t-0 border-green-700/30 mt-0">
      <div class="container mx-auto text-center text-xs md:text-sm">
        <p>© {new Date().getFullYear()} New Cabalan National High School. All rights reserved.</p>
        <p class="mt-1 md:mt-2 text-yellow-300/80 text-[10px] md:text-xs">Empowering students through education since 1979</p>
      </div>
    </footer>
<style>
    /* Global body font should ideally be set via Tailwind's global base styles if you use it for the whole site.
       If you keep this, the `font-montserrat` classes on specific elements will override it. */
    :global(body) {
        margin: 0;
        /* font-family: Arial, Helvetica, sans-serif; <-- Consider removing this line if using Tailwind's font-sans */
        overflow-x: hidden;
        overflow-y: auto;
        background-color: #096B68;
    }

    .page-container {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
        background-color: #096B68; /* Note: #096B68 is an invalid color code (missing # or too short). Assuming you meant #096B68 */
    }

    main {
        flex: 1;
        background-size: cover;
        background-position: center;
        display: flex;
        flex-direction: column;
        align-items: center;
        padding-top: 10px;
        padding-bottom: 10px;
        position: relative;
        overflow: hidden;
    }

    .school-logo {
        width: 220px;
        height: 220px;
        object-fit: contain;
        margin-bottom: 10px;
        filter: drop-shadow(0 0 5px rgba(0, 0, 0, 0.5));
    }

    .title-container {
        display: flex;
        justify-content: center;
        align-items: center;
        width: 90%;
        max-width: 800px;
        margin: 0 auto 15px auto;
        padding: 0 10px;
        box-sizing: border-box;
        gap: 15px;
        position: relative;
    }

    .announcement-title {
        color: rgb(227, 229, 224);
        font-size: 4.5em;
        /* font-weight: bold; <-- Handled by Tailwind's font-extrabold class */
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);
        margin: 0;
        white-space: nowrap;
        padding: 5px 30px;
    }

    .announcement-container {
        position: relative;
        width: 90%; /* This width seems very large, consider adjusting for typical designs */
        max-width: 1050px;
        margin-bottom: 10px;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .announcement-box {
        background-color: #e6e6e6;
        border: 20px solid #096B68;
        border-radius: 10px;
        width: 100%; /* This width also seems very large, consider adjusting */
        padding: 1.5rem 2rem;
        height: 500px;
        overflow-y: auto;
        box-sizing: border-box;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.4);
        color: #333;
        /* text-align: justify; <-- REMOVE THIS LINE to allow text-align: center on child elements */
        position: relative;
        z-index: 1;
        font-size: 1.25rem; /* Increased font size */
        text-align: justify;
    }

    /* Styles for dynamic announcement content */
    .announcement-box h2 {
        font-size: 2.2em;
        color: #005f4f;
        margin-bottom: 10px;
        /* text-align: center; <-- This is fine, but `text-center` class now takes precedence */
    }

    .announcement-date {
        font-size: 0.9em;
        color: #666;
        /* text-align: center; <-- This is fine, but `text-center` class now takes precedence */
        margin-bottom: 20px;
        border-bottom: 1px solid #eee;
        padding-bottom: 10px;
    }

    .announcement-navigation {
        display: flex;
        justify-content: center;
        gap: 20px;
        width: 100%;
        margin-top: 20px;
        padding: 0 1rem;
        box-sizing: border-box;
    }

    .announcement-navigation button {
		background-color: #326b09;
		border: 2px solid black;
		border-radius: 8px;
		width: 60px;
		height: 60px;
		display: flex;
		justify-content: center;
		align-items: center;
		cursor: pointer;
		transition: background-color 0.3s;
		color: white;
	}
	.announcement-navigation button:disabled {
		background-color: #6b6109;
		border-color: #050505;
		cursor: not-allowed;
		opacity: 0.5;
	}
	.announcement-navigation button:hover:not(:disabled) {
		background-color: #148a0c;
	}
    /* Responsive Design */
    @media (max-width: 768px) {
        .announcement-title {
            font-size: 3em;
        }

        .announcement-box {
            padding: 20px;
            border-width: 15px;
        }

        .announcement-box h2 {
            font-size: 1.8em;
        }

        .announcement-content p {
            font-size: 1.1em;
        }

        .announcement-navigation button {
            width: 50px;
            height: 50px;
        }
    }


    @media (max-width: 480px) {
        .school-logo {
            width: 150px;
            height: 150px;
        }
        .announcement-title {
            font-size: 2em;
            padding: 5px 15px;
        }
        .announcement-box {
            padding: 15px;
            border-width: 10px;
            min-height: 400px;
        }
        .announcement-box h2 {
            font-size: 1.5em;
        }
        .announcement-content p {
            font-size: 1em;
            line-height: 1.6;
        }
        .announcement-navigation button {
            width: 45px;
            height: 45px;
        }
        .announcement-attachments {
            margin-top: 40px;
        }
    }
</style>