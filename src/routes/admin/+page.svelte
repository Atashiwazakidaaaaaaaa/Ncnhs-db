<script lang="ts">
	import { onMount } from 'svelte';
	import Navbar from '$lib/components/navbar.svelte';
	// --- All your existing interfaces and constants ---
	interface Announcement {
		id: string;
		title: string;
		date: string;
		content: string;
	}
	const API_URL = 'http://localhost/PHP/api/api.php';

	// --- All your existing state variables ---
	let announcements: Announcement[] = [];
	let isLoading = true;
	let errorMessage: string | null = null;
	let selectedAnnouncementId: string | undefined;
	let isEditingTitle = false;
	let isEditingDate = false;
	let isEditingContent = false;
	let tempTitle = '';
	let tempDate = '';
	let tempContent = '';
	let isManageMenuOpen = false;

	// --- Your existing saveChanges function (no changes needed) ---
	async function saveChanges(announcementToSave: Announcement) {
		// ... same as before
		try {
			const response = await fetch(API_URL, {
				method: 'PUT',
				headers: {
					'Content-Type': 'application/json'
				},
				body: JSON.stringify(announcementToSave)
			});

			if (!response.ok) {
				throw new Error('Failed to save changes to the server.');
			}

			// Optionally show a success message to the user
			console.log('Changes saved successfully!');
			return true;
		} catch (error) {
			console.error('Error saving changes:', error);
			errorMessage = 'Could not save changes. Please try again.';
			return false;
		}
	}

	// --- UPDATED: Function to handle creating a new announcement ---
	async function handleNewAnnouncement() {
		if (confirm('Are you sure you want to create a new announcement?')) {
			const newAnn: Omit<Announcement, 'id'> = {
				title: 'New Announcement Title',
				date: new Date().toISOString().split('T')[0], // Format as YYYY-MM-DD
				content: ''
			};

			try {
				const response = await fetch(API_URL, {
					method: 'POST',
					headers: { 'Content-Type': 'application/json' },
					body: JSON.stringify(newAnn)
				});

				if (!response.ok) {
					throw new Error('Failed to create new announcement on the server.');
				}

				const result = await response.json();
				const createdAnn: Announcement = { ...newAnn, id: result.id };

				announcements = [createdAnn, ...announcements];
				selectedAnnouncementId = createdAnn.id;
			} catch (error) {
				console.error('Error creating announcement:', error);
				errorMessage = 'Could not create a new announcement.';
			}
		}
	}

	// --- UPDATED: Function to handle deleting an announcement ---
	async function handleDeleteAnnouncement() {
		if (!currentAnnouncement) return;

		if (confirm('Are you sure you want to permanently delete this announcement?')) {
			try {
				// Append the ID as a query parameter for a DELETE request
				const response = await fetch(`${API_URL}?id=${currentAnnouncement.id}`, {
					method: 'DELETE'
				});

				if (!response.ok) {
					throw new Error('Failed to delete announcement on the server.');
				}

				// If successful, remove it from the local list
				announcements = announcements.filter((ann) => ann.id !== currentAnnouncement.id);

				if (announcements.length > 0) {
					selectedAnnouncementId = announcements[0].id;
				} else {
					selectedAnnouncementId = undefined;
				}
			} catch (error) {
				console.error('Error deleting announcement:', error);
				errorMessage = 'Could not delete the announcement.';
			}
		}
	}

	// --- Your existing onMount and reactive statements (no changes needed) ---
	onMount(async () => {
		try {
			const response = await fetch(API_URL);
			if (!response.ok) throw new Error(`Server responded with status: ${response.status}`);

			announcements = await response.json();

			if (announcements.length > 0) {
				selectedAnnouncementId = announcements[0].id;
			}
		} catch (error) {
			console.error('Error fetching announcements:', error);
			errorMessage = 'Could not load announcements from the server.';
		} finally {
			isLoading = false;
		}
	});

	$: currentAnnouncement = announcements.find((ann) => ann.id === selectedAnnouncementId);
	$: currentIndex = announcements.findIndex((ann) => ann.id === selectedAnnouncementId);
	$: hasPrevious = currentIndex > 0;
	$: hasNext = currentIndex < announcements.length - 1;

	$: if (currentAnnouncement) {
		isEditingTitle = false;
		isEditingDate = false;
		isEditingContent = false;
	}

	function goToPrevious() {
		if (hasPrevious) selectedAnnouncementId = announcements[currentIndex - 1].id;
	}

	function goToNext() {
		if (hasNext) selectedAnnouncementId = announcements[currentIndex + 1].id;
	}
</script>

<!-- The rest of your HTML remains exactly the same -->
<div class="page-container">
	<Navbar />
	<main style="background-image: url('/ncnhs.jpg');">
		<!-- ... all your existing HTML ... -->
		<img src="/logo.png" alt="School Logo" class="school-logo" />
		<div class="title-container">
			<h3 class="announcement-title font-serif font-extrabold">ADMIN PANEL</h3>
			{#if announcements.length > 0}
				<div class="action-buttons-container">
					<button on:click={() => isManageMenuOpen = !isManageMenuOpen} class="action-btn manage-btn"> Manage </button>
					{#if isManageMenuOpen}
						<div class="manage-menu">
							<button on:click={() => { handleNewAnnouncement(); isManageMenuOpen = false; }} class="action-btn add-btn"> New </button>
							<button on:click={() => { handleDeleteAnnouncement(); isManageMenuOpen = false; }} class="action-btn delete-btn"> Delete </button>
						</div>
					{/if}
				</div>
			{/if}
		</div>

		{#if isLoading}
			<p class="loading-message">Loading Announcements...</p>
		{:else if errorMessage}
			<div class="announcement-box">
				<p class="error-message">{errorMessage}</p>
			</div>
		{:else if currentAnnouncement}
			<!-- ... all your existing HTML for displaying the announcement ... -->
			<div class="announcement-container">
				<div class="announcement-box">
					<!-- EDITABLE TITLE SECTION -->
					<div class="editable-field">
						{#if isEditingTitle}
							<input type="text" bind:value={tempTitle} class="editable-input title-input" />
							<button
								class="field-btn save-btn"
								on:click={async () => {
									currentAnnouncement.title = tempTitle;
									if (await saveChanges(currentAnnouncement)) {
										isEditingTitle = false;
									}
								}}>Save</button
							>
							<button class="field-btn cancel-btn" on:click={() => (isEditingTitle = false)}
								>Cancel</button
							>
						{:else}
							<h2 class="font-serif font-bold">{currentAnnouncement.title}</h2>
							<button
								class="field-btn edit-btn"
								on:click={() => {
									tempTitle = currentAnnouncement.title;
									isEditingTitle = true;
								}}>Edit</button
							>
						{/if}
					</div>

					<!-- EDITABLE DATE SECTION -->
					<div class="editable-field date-field">
						{#if isEditingDate}
							<span class="date-prefix">Published:</span>
							<input type="date" bind:value={tempDate} class="editable-input date-input" />
							<button
								class="field-btn save-btn"
								on:click={async () => {
									currentAnnouncement.date = tempDate;
									if (await saveChanges(currentAnnouncement)) {
										isEditingDate = false;
									}
								}}>Save</button
							>
							<button class="field-btn cancel-btn" on:click={() => (isEditingDate = false)}
								>Cancel</button
							>
						{:else}
							<p class="announcement-date font-sans font-bold">
								Published: {currentAnnouncement.date}
							</p>
							<button
								class="field-btn edit-btn"
								on:click={() => {
									tempDate = currentAnnouncement.date;
									isEditingDate = true;
								}}>Edit</button
							>
						{/if}
					</div>

					<!-- EDITABLE CONTENT SECTION -->
					<div class="editable-field content-field">
						{#if isEditingContent}
							<textarea bind:value={tempContent} class="editable-input content-input" />
							<div class="content-buttons">
								<button
									class="field-btn save-btn"
									on:click={async () => {
										currentAnnouncement.content = tempContent;
										if (await saveChanges(currentAnnouncement)) {
											isEditingContent = false;
										}
									}}>Save</button
								>
								<button class="field-btn cancel-btn" on:click={() => (isEditingContent = false)}
									>Cancel</button
								>
							</div>
						{:else}
							<div class="announcement-content font-sans">
								{@html currentAnnouncement.content}
							</div>
							<button
								class="field-btn edit-btn"
								on:click={() => {
									tempContent = currentAnnouncement.content;
									isEditingContent = true;
								}}>Put Content</button
							>
						{/if}
					</div>
				</div>
				<!-- Main Navigation -->
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
			<div class="announcement-box" style="display: flex; flex-direction: column; align-items: center;">
				<p class="no-announcement-message">No announcements available.</p>
				<button on:click={handleNewAnnouncement} class="action-btn add-btn" style="margin-top: 10px;">
					Create First Announcement
				</button>
			</div>
		{/if}
	</main>
</div>

<footer
	class="bg-green-900/80 text-white py-3 md:py-4 backdrop-blur-md border-t-0 border-green-700/30 mt-0"
>
	<!-- ... footer content ... -->
	<div class="container mx-auto text-center text-xs md:text-sm">
		<p>© {new Date().getFullYear()} New Cabalan National High School. All rights reserved.</p>
		<p class="mt-1 md:mt-2 text-yellow-300/80 text-[10px] md:text-xs">
			Empowering students through education since 1979
		</p>
	</div>
</footer>

<!-- All your existing styles remain the same -->
<style>
	/* ... all your existing styles ... */
	/* --- NEW UX STYLES --- */
	.loading-message,
	.error-message {
		color: white;
		background-color: rgba(0, 0, 0, 0.5);
		padding: 20px;
		border-radius: 8px;
		font-size: 1.2em;
		text-align: center;
	}
	.error-message {
		background-color: #f44336; /* Red background for errors */
		color: white;
		font-weight: bold;
	}
	/* --- STYLES FOR PER-FIELD EDITING --- */
	.editable-field {
		display: flex;
		align-items: center;
		gap: 15px;
		margin-bottom: 20px;
		width: 100%;
	}
	.date-field {
		border-bottom: 1px solid #eee;
		padding-bottom: 20px;
	}
	.content-field {
		flex-direction: column;
		align-items: flex-start;
	}
	.editable-input {
		border: 1px solid #ccc;
		padding: 8px;
		border-radius: 4px;
		font-size: 1em;
		flex-grow: 1;
	}
	.title-input {
		font-size: 1.5em;
		font-weight: bold;
	}
	.date-input {
		flex-grow: 0;
	}
	.content-input {
		width: 100%;
		height: 300px;
		resize: vertical;
	}
	.field-btn {
		padding: 6px 12px;
		border-radius: 5px;
		border: 1px solid transparent;
		cursor: pointer;
		font-size: 0.8em;
		font-weight: bold;
		white-space: nowrap;
	}
	.edit-btn {
		background-color: #e0e0e0;
		border-color: #ccc;
		color: #333;
	}
	.save-btn {
		background-color: #4caf50;
		color: white;
	}
	.cancel-btn {
		background-color: #f44336;
		color: white;
	}
	.content-buttons {
		margin-top: 10px;
		display: flex;
		gap: 10px;
	}
	.date-prefix {
		font-weight: bold;
	}
	h2 {
		margin: 0;
		flex-grow: 1;
	}

	/* --- ACTION BUTTONS & POPUP MENU STYLES --- */
	.action-buttons-container {
		position: relative;
		z-index: 10;
		display: flex;
		gap: 5px;
		justify-content: flex-end;
	}
	.action-btn {
		border-radius: 8px;
		padding: 10px 20px;
		font-weight: bold;
		color: white;
		border: none;
		cursor: pointer;
		text-align: center;
	}
	.add-btn {
		background-color: #4caf50;
	}
	.delete-btn {
		background-color: #f44336;
	}
	.manage-btn {
		background-color: #9f9a00; 
	}
	.manage-menu {
		position: absolute;
		top: 100%;
		right: 0;
		background-color: white;
		border: 1px solid #ccc;
		border-radius: 5px;
		display: flex;
		flex-direction: column;
		z-index: 20;
		width: 120px;
		padding: 5px;
		box-sizing: border-box;
	}
	.manage-menu button {
		width: 100%;
		border: none;
		margin: 0;
	}
	.manage-menu button:not(:last-child) {
		margin-bottom: 5px;
	}
	.manage-menu .add-btn:hover {
		background-color: #45a049; /* Darker green */
	}
	.manage-menu .delete-btn:hover {
		background-color: #e53935; /* Darker red */
	}
	.no-announcement-message {
		color: grey;
		font-weight: 500;
	}

	/* --- PREVIOUS/NEXT NAVIGATION BUTTONS --- */
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

	/* --- GENERAL LAYOUT STYLES (Unchanged) --- */
	:global(body) {
		margin: 0;
		background-color: #096b68;
	}
	.page-container {
		display: flex;
		flex-direction: column;
		min-height: 100vh;
	}
	main {
		flex: 1;
		background-size: cover;
		background-position: center;
		display: flex;
		flex-direction: column;
		align-items: center;
		padding: 10px;
	}
	.school-logo {
		width: 220px;
		height: 220px;
		object-fit: contain;
		margin-bottom: 10px;
	}
	.title-container {
		text-align: center;
		margin-bottom: 15px;
	}
	.announcement-title {
		color: rgb(190, 166, 4);
		font-size: 4.5em;
		text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);
	}
	.announcement-container {
		position: relative;
		width: 90%;
		max-width: 1050px;
		display: flex;
		flex-direction: column;
		align-items: center;
	}
	.announcement-box {
		background-color: #e6e6e6;
		border: 20px solid #096b68;
		border-radius: 15px;
		width: 75%;
		padding: 1.5rem 2rem;
		box-sizing: border-box;
		position: relative;
	}
	.announcement-date {
		margin: 0;
		flex-grow: 1;
	}
	.announcement-content {
		width: 100%;
		text-align: justify;
	}

	.announcement-navigation {
		display: flex;
		justify-content: center;
		gap: 20px;
		width: 100%;
		margin-top: 20px;
		padding: 0 1rem;
		box-sizing: border-box;
		position: relative;
	}
	/* --- RESPONSIVE DESIGN --- */
	@media (max-width: 768px) {
		.announcement-title {
			font-size: 3em;
		}
		.announcement-box {
			width: 90%;
			padding: 1rem;
		}
		.editable-field {
			flex-direction: column;
			align-items: stretch;
			gap: 10px;
		}
		.editable-field h2,
		.editable-field p {
			text-align: center;
		}
		.editable-input {
			width: 100%;
		}
		.field-btn {
			width: 100%;
			box-sizing: border-box;
		}
		.content-buttons {
			flex-direction: column;
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
		}
		.announcement-box {
			width: 95%;
			border-width: 10px;
		}
		.title-input {
			font-size: 1.2em;
		}
		.content-input {
			height: 200px;
		}
		.announcement-navigation button {
			width: 45px;
			height: 45px;
		}
	}
</style>