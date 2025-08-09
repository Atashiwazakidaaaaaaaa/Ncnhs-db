<script lang="ts">
	import { onMount } from 'svelte';
	import { fly } from 'svelte/transition';
	import { enhance } from '$app/forms';
	import type { PageData, ActionData } from './$types';

	let { data, form }: { data: PageData; form: ActionData } = $props();

	// Use the announcements from your existing system
	let announcements = data.announcements || [];
	let currentAnnouncementIndex = 0;
	let isLoading = false;
	let errorMessage: string | null = null;

	// Admin authentication state (using your existing pattern)
	let isAdminLoggedIn = $state(false);
	let cookieCheckInterval: NodeJS.Timeout | null = null;

	// Edit states
	let isEditingTitle = false;
	let isEditingDate = false;
	let isEditingContent = false;
	let selectedAnnouncementId: number | null = null;
	let tempTitle = '';
	let tempDate = '';
	let tempContent = '';

	// New announcement modal
	let showNewAnnouncementModal = false;
	let newAnnouncement = {
		title: '',
		display_date: '',
		content: '',
		category: 'general'
	};

	// Reactive statements
	let currentAnnouncement = $derived(announcements[currentAnnouncementIndex]);
	let hasNextAnnouncement = $derived(currentAnnouncementIndex < announcements.length - 1);
	let hasPreviousAnnouncement = $derived(currentAnnouncementIndex > 0);

	// Check admin login status (using your existing pattern)
	function checkAdminLogin() {
		if (typeof document !== 'undefined') {
			const adminCookie = document.cookie
				.split('; ')
				.find(row => row.startsWith('admin_logged_in='));
			isAdminLoggedIn = adminCookie ? adminCookie.split('=')[1] === 'true' : false;
		}
	}

	onMount(() => {
		checkAdminLogin();
		cookieCheckInterval = setInterval(checkAdminLogin, 500);
		return () => {
			if (cookieCheckInterval) {
				clearInterval(cookieCheckInterval);
			}
		};
	});

	// Navigation functions
	function goToNextAnnouncement() {
		if (hasNextAnnouncement) {
			currentAnnouncementIndex++;
			cancelEdit();
		}
	}

	function goToPreviousAnnouncement() {
		if (hasPreviousAnnouncement) {
			currentAnnouncementIndex--;
			cancelEdit();
		}
	}

	function goToAnnouncementIndex(index: number) {
		if (index >= 0 && index < announcements.length) {
			currentAnnouncementIndex = index;
			cancelEdit();
		}
	}

	// Edit functions
	function startEditingTitle(announcementId: number) {
		const announcement = announcements.find((a: any) => a.id === announcementId);
		if (announcement) {
			selectedAnnouncementId = announcementId;
			tempTitle = announcement.title;
			isEditingTitle = true;
		}
	}

	function startEditingDate(announcementId: number) {
		const announcement = announcements.find((a: any) => a.id === announcementId);
		if (announcement) {
			selectedAnnouncementId = announcementId;
			tempDate = announcement.display_date;
			isEditingDate = true;
		}
	}

	function startEditingContent(announcementId: number) {
		const announcement = announcements.find((a: any) => a.id === announcementId);
		if (announcement) {
			selectedAnnouncementId = announcementId;
			tempContent = announcement.content;
			isEditingContent = true;
		}
	}

	function cancelEdit() {
		isEditingTitle = false;
		isEditingDate = false;
		isEditingContent = false;
		selectedAnnouncementId = null;
		tempTitle = '';
		tempDate = '';
		tempContent = '';
	}

	// Modal functions
	function openNewAnnouncementModal() {
		showNewAnnouncementModal = true;
	}

	function closeNewAnnouncementModal() {
		showNewAnnouncementModal = false;
		newAnnouncement = {
			title: '',
			display_date: '',
			content: '',
			category: 'general'
		};
	}

	// Format date helper
	function formatDate(dateString: string) {
		const date = new Date(dateString);
		return date.toLocaleDateString('en-US', { 
			year: 'numeric', 
			month: 'long', 
			day: 'numeric' 
		});
	}

	// Handle keyboard navigation
	function handleKeydown(event: KeyboardEvent) {
		if (event.key === 'ArrowLeft') {
			goToPreviousAnnouncement();
		} else if (event.key === 'ArrowRight') {
			goToNextAnnouncement();
		}
	}

	let visible = true;
</script>

<svelte:head>
	<title>Admin Panel - New Cabalan National High School</title>
</svelte:head>

<svelte:window on:keydown={handleKeydown} />

<!-- Use your existing layout pattern -->
<div class="relative min-h-screen bg-cover bg-center font-sans" 
     style="background-image: url('{isAdminLoggedIn ? '/adminbackground.png' : '/ncnhs.jpg'}'); background-attachment: scroll;">
	<div class="absolute inset-0 bg-black opacity-40"></div>
	
	<div class="relative z-10 flex min-h-screen flex-col">
		<main class="flex flex-grow flex-col items-center pb-0">
			{#if visible}
				<div class="w-full max-w-6xl mx-auto mb-8 flex flex-col" in:fly={{ y: 100, duration: 800, delay: 600 }}>
					<!-- Header with your existing green gradient -->
					<div class="bg-gradient-to-r from-green-600 to-green-700 px-3 pt-3 pb-5 shadow-2xl min-h-[70vh] rounded-b-xl">
						<div class="bg-white p-4 md:p-6 rounded-xl h-full flex flex-col">
							
							<!-- Admin Panel Title -->
							<div class="text-center mb-6">
								<h1 class="text-3xl md:text-4xl font-bold text-green-800 mb-2">Admin Panel - Announcements</h1>
								<p class="text-green-600 text-lg">Manage school announcements</p>
							</div>

							<!-- Admin Controls -->
							{#if isAdminLoggedIn}
								<div class="mb-4 flex justify-between items-center">
									<div class="text-sm text-gray-600">
										{#if announcements.length > 0}
											Showing announcement {currentAnnouncementIndex + 1} of {announcements.length}
										{/if}
									</div>
									<button
										on:click={openNewAnnouncementModal}
										class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium"
									>
										+ New Announcement
									</button>
								</div>
							{/if}

							<!-- Main Content Area -->
							<div class="flex-1 flex flex-col">
								{#if announcements.length === 0}
									<div class="flex-1 flex items-center justify-center">
										<div class="text-center">
											<p class="text-gray-500 text-lg mb-4">No announcements available.</p>
											{#if isAdminLoggedIn}
												<button
													on:click={openNewAnnouncementModal}
													class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
												>
													Create First Announcement
												</button>
											{/if}
										</div>
									</div>
								{:else if currentAnnouncement}
									<div class="flex-1 flex flex-col">
										<!-- Single Announcement Display -->
										<div class="bg-gray-50 rounded-xl p-6 border border-gray-200 hover:shadow-md transition-shadow duration-200 flex-1" in:fly={{ x: 50, duration: 300 }}>
											
											<!-- Title Section -->
											<div class="flex justify-between items-start mb-4">
												{#if isAdminLoggedIn}
													<div class="flex items-center gap-2">
														<form method="POST" action="?/deleteAnnouncement" use:enhance>
															<input type="hidden" name="id" value={currentAnnouncement.id} />
															<button 
																type="submit"
																class="text-red-600 hover:text-red-800 p-1 rounded hover:bg-red-100 transition-colors"
																title="Delete announcement"
																on:click={(e) => {
																	if (!confirm('Are you sure you want to delete this announcement?')) {
																		e.preventDefault();
																	}
																}}
															>
																<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
																	<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
																</svg>
															</button>
														</form>
													</div>
												{/if}
											</div>
											
											<!-- Title Display/Edit -->
											{#if isEditingTitle && selectedAnnouncementId === currentAnnouncement.id}
												<form method="POST" action="?/updateAnnouncement" use:enhance>
													<input type="hidden" name="id" value={currentAnnouncement.id} />
													<div class="flex items-center gap-2 mb-4">
														<input 
															type="text" 
															name="title" 
															bind:value={tempTitle} 
															class="flex-1 text-2xl font-bold text-green-800 bg-white border border-gray-300 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-green-500"
														/>
														<button type="submit" class="text-green-600 hover:text-green-800 p-1" title="Save">
															<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
																<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
															</svg>
														</button>
														<button type="button" on:click={cancelEdit} class="text-red-600 hover:text-red-800 p-1" title="Cancel">
															<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
																<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
															</svg>
														</button>
													</div>
												</form>
											{:else}
												<div class="flex items-center gap-2 mb-4">
													<h2 class="text-2xl font-bold text-green-800 flex-1">{currentAnnouncement.title}</h2>
													{#if isAdminLoggedIn}
														<button 
															on:click={() => startEditingTitle(currentAnnouncement.id)} 
															class="text-blue-600 hover:text-blue-800 p-1 rounded hover:bg-blue-100 transition-colors"
															title="Edit title"
														>
															✏️
														</button>
													{/if}
												</div>
											{/if}

											<!-- Date Display/Edit -->
											{#if isEditingDate && selectedAnnouncementId === currentAnnouncement.id}
												<form method="POST" action="?/updateAnnouncement" use:enhance>
													<input type="hidden" name="id" value={currentAnnouncement.id} />
													<div class="flex items-center gap-2 mb-3">
														<span class="text-sm text-gray-600">📅</span>
														<input 
															type="text" 
															name="display_date" 
															bind:value={tempDate} 
															class="text-sm text-gray-600 bg-white border border-gray-300 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-green-500"
														/>
														<button type="submit" class="text-green-600 hover:text-green-800 p-1" title="Save">
															<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
																<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
															</svg>
														</button>
														<button type="button" on:click={cancelEdit} class="text-red-600 hover:text-red-800 p-1" title="Cancel">
															<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
																<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
															</svg>
														</button>
													</div>
												</form>
											{:else}
												<div class="flex items-center gap-2 text-sm text-gray-600 mb-3">
													<span>📅</span>
													<span>{currentAnnouncement.display_date}</span>
													{#if isAdminLoggedIn}
														<button 
															on:click={() => startEditingDate(currentAnnouncement.id)} 
															class="text-blue-600 hover:text-blue-800 opacity-70 hover:opacity-100"
															title="Edit date"
														>
															✏️
														</button>
													{/if}
												</div>
											{/if}

											<!-- Category Badge -->
											<div class="mb-4">
												<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
													{currentAnnouncement.category?.charAt(0).toUpperCase() + currentAnnouncement.category?.slice(1) || 'General'}
												</span>
											</div>

											<!-- Content Display/Edit -->
											{#if isEditingContent && selectedAnnouncementId === currentAnnouncement.id}
												<form method="POST" action="?/updateAnnouncement" use:enhance>
													<input type="hidden" name="id" value={currentAnnouncement.id} />
													<div class="mb-4">
														<textarea 
															name="content" 
															bind:value={tempContent} 
															rows="10"
															class="w-full text-gray-700 bg-white border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
															placeholder="Enter announcement content..."
														></textarea>
														<div class="flex gap-2 mt-2">
															<button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition-colors">
																Save
															</button>
															<button type="button" on:click={cancelEdit} class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400 transition-colors">
																Cancel
															</button>
														</div>
													</div>
												</form>
											{:else}
												<div class="text-gray-700 leading-relaxed prose prose-sm max-w-none">
													{@html currentAnnouncement.content}
													{#if isAdminLoggedIn}
														<button 
															on:click={() => startEditingContent(currentAnnouncement.id)} 
															class="ml-2 text-blue-600 hover:text-blue-800 text-xs opacity-70 hover:opacity-100"
															title="Edit content"
														>
															✏️
														</button>
													{/if}
												</div>
											{/if}

											<!-- Display image if available -->
											{#if currentAnnouncement.attachment}
												<div class="mt-4">
													<img 
														src={currentAnnouncement.attachment} 
														alt="Related to {currentAnnouncement.title}" 
														class="max-w-full h-auto rounded-lg shadow-md border border-gray-200 hover:shadow-lg transition-shadow duration-200"
														loading="lazy"
													/>
												</div>
											{/if}
										</div>

										<!-- Navigation Controls -->
										<div class="flex justify-center items-center gap-4 mt-6">
											<button
												on:click={goToPreviousAnnouncement}
												disabled={!hasPreviousAnnouncement}
												class="flex items-center justify-center w-12 h-12 bg-green-600 text-white rounded-full hover:bg-green-700 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors"
											>
												<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
													<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
												</svg>
											</button>

											<div class="flex items-center gap-2">
												{#each announcements as _, index}
													<button
														on:click={() => goToAnnouncementIndex(index)}
														class="w-3 h-3 rounded-full {index === currentAnnouncementIndex ? 'bg-green-600' : 'bg-gray-300'} hover:bg-green-500 transition-colors"
													></button>
												{/each}
											</div>

											<button
												on:click={goToNextAnnouncement}
												disabled={!hasNextAnnouncement}
												class="flex items-center justify-center w-12 h-12 bg-green-600 text-white rounded-full hover:bg-green-700 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors"
											>
												<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
													<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
												</svg>
											</button>
										</div>
									</div>
								{/if}
							</div>
						</div>
					</div>
				</div>
			{/if}
		</main>
	</div>
</div>

<!-- New Announcement Modal -->
{#if showNewAnnouncementModal}
	<div class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 pt-20 z-50" 
		on:click={(e) => e.target === e.currentTarget && closeNewAnnouncementModal()}>
		<div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[80vh] overflow-y-auto" 
			on:click|stopPropagation>
			<div class="bg-gradient-to-r from-green-600 to-green-700 text-white p-6 rounded-t-2xl">
				<div class="flex justify-between items-center">
					<h3 class="text-xl font-semibold">Create New Announcement</h3>
					<button on:click={closeNewAnnouncementModal} class="text-white hover:text-gray-200">
						<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
						</svg>
					</button>
				</div>
			</div>
			
			<!-- Modal Body -->
			<form method="POST" action="?/createAnnouncement" enctype="multipart/form-data" use:enhance={() => {
				return async ({ update }) => {
					await update();
					closeNewAnnouncementModal();
				};
			}}>
				<div class="p-6 space-y-4">
					<div>
						<label for="title" class="block text-sm font-medium text-gray-700 mb-2">Title</label>
						<input
							id="title"
							name="title"
							type="text"
							bind:value={newAnnouncement.title}
							required
							class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
							placeholder="Enter announcement title"
						/>
					</div>
					
					<div>
						<label for="display_date" class="block text-sm font-medium text-gray-700 mb-2">Display Date</label>
						<input
							id="display_date"
							name="display_date"
							type="text"
							bind:value={newAnnouncement.display_date}
							required
							class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
							placeholder="e.g., December 25th, 2024"
						/>
					</div>
					
					<div>
						<label for="content" class="block text-sm font-medium text-gray-700 mb-2">Content</label>
						<textarea
							id="content"
							name="content"
							bind:value={newAnnouncement.content}
							rows="6"
							class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
							placeholder="Enter announcement content"
						></textarea>
					</div>
					
					<div>
						<label for="category" class="block text-sm font-medium text-gray-700 mb-2">Category</label>
						<select
							id="category"
							name="category"
							bind:value={newAnnouncement.category}
							class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
						>
							<option value="general">General</option>
							<option value="academic">Academic</option>
							<option value="event">Event</option>
							<option value="maintenance">Maintenance</option>
						</select>
					</div>

					<div>
						<label for="image" class="block text-sm font-medium text-gray-700 mb-2">Image (Optional)</label>
						<input
							id="image"
							name="image"
							type="file"
							accept="image/*"
							class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
						/>
					</div>
				</div>
				
				<div class="p-6 pt-0 flex gap-3 justify-end">
					<button
						type="button"
						on:click={closeNewAnnouncementModal}
						class="px-4 py-2 text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
					>
						Cancel
					</button>
					<button
						type="submit"
						class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
					>
						Create Announcement
					</button>
				</div>
			</form>
		</div>
	</div>
{/if}

<style>
	/* Use Tailwind styles for most styling, with custom overrides here if needed */
	:global(.prose) {
		max-width: none !important;
	}

	:global(.prose h1) {
		color: #1f2937;
		margin-bottom: 1rem;
	}

	:global(.prose h2) {
		color: #1f2937;
		margin-bottom: 0.75rem;
	}

	:global(.prose p) {
		margin-bottom: 1rem;
		line-height: 1.6;
	}

	/* Custom styles for backward compatibility */
	.bg-cover {
		background-size: cover;
	}

	.bg-center {
		background-position: center;
	}
</style>