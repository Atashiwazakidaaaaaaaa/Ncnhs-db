import { fail } from '@sveltejs/kit';
import type { Actions, PageServerLoad } from './$types';
import { db } from '$lib/server/db';
import { announcements } from '$lib/server/db/schema';
import { eq, desc } from 'drizzle-orm';

export const load: PageServerLoad = async () => {
	try {
		// Get database instance
		const database = await db();
		
		// Fetch all active announcements, ordered by most recent first
		const allAnnouncements = await database
			.select()
			.from(announcements)    
			.where(eq(announcements.is_active, 1))
			.orderBy(desc(announcements.created_at));

		return {
			announcements: allAnnouncements
		};
	} catch (error) {
		console.error('Error loading announcements:', error);
		return {
			announcements: []
		};
	}
};

export const actions: Actions = {
	createAnnouncement: async ({ request }) => {
		const formData = await request.formData();
		const title = formData.get('title') as string;
		const content = formData.get('content') as string;
		const displayDate = formData.get('displayDate') as string;
		const category = formData.get('category') as string || 'general';
		const author = formData.get('author') as string || 'Administrator';
		const imageFile = formData.get('image') as File;

		// Debug logging
		console.log('Form data received:');
		console.log('- title:', title);
		console.log('- content:', content?.substring(0, 50) + '...');
		console.log('- displayDate:', displayDate);
		console.log('- category:', category);
		console.log('- imageFile:', imageFile);
		console.log('- imageFile name:', imageFile?.name);
		console.log('- imageFile size:', imageFile?.size);
		console.log('- imageFile type:', imageFile?.type);

		if (!title || !content || !displayDate) {
			return fail(400, { error: 'Title, content, and date are required.' });
		}

		try {
			// Get database instance
			const database = await db();
			
			// Process image if provided
			let imageData = null;
			let imageFilename = null;
			if (imageFile && imageFile.size > 0) {
				try {
					console.log('Processing image file:', imageFile.name);
					// Convert image to base64
					const imageBuffer = await imageFile.arrayBuffer();
					const base64Image = Buffer.from(imageBuffer).toString('base64');
					const mimeType = imageFile.type;
					imageData = `data:${mimeType};base64,${base64Image}`;
					imageFilename = imageFile.name;
					console.log('Image processed successfully. Filename:', imageFilename);
					console.log('Base64 data length:', base64Image.length);
				} catch (imageError) {
					console.error('Error processing image:', imageError);
					// Don't fail the entire request if image processing fails
				}
			} else {
				console.log('No image file provided or file size is 0');
			}
			
			// Create the announcement with image data
			console.log('Inserting announcement with:', {
				title,
				content: content?.substring(0, 50) + '...',
				display_date: displayDate,
				category,
				author,
				attachment: imageData ? 'BASE64_DATA_PROVIDED' : null,
				image_filename: imageFilename,
				is_active: 1
			});
			
			await database.insert(announcements).values({
				title,
				content,
				date_posted: new Date().toISOString().split('T')[0], // MySQL date format YYYY-MM-DD
				display_date: displayDate,
				category,
				author,
				attachment: imageData,
				image_filename: imageFilename,
				is_active: 1
			} as any); // Type assertion to bypass strict typing

			return { success: true, message: 'Announcement created successfully!' };
		} catch (error) {
			console.error('Error creating announcement:', error);
			return fail(500, { error: 'Failed to create announcement.' });
		}
	},

	updateAnnouncement: async ({ request }) => {
		const formData = await request.formData();
		const id = parseInt(formData.get('id') as string);
		const title = formData.get('title') as string;
		const content = formData.get('content') as string;
		const displayDate = formData.get('displayDate') as string;
		const category = formData.get('category') as string;
		const imageFile = formData.get('image') as File;

		if (!id || !title || !content || !displayDate) {
			return fail(400, { error: 'All fields are required.' });
		}

		try {
			// Get database instance
			const database = await db();
			
			// Prepare update data
			const updateData: any = {
				title,
				content,
				display_date: displayDate,
				category,
				updated_at: new Date()
			};

			// Process image if provided
			if (imageFile && imageFile.size > 0) {
				try {
					// Convert image to base64
					const imageBuffer = await imageFile.arrayBuffer();
					const base64Image = Buffer.from(imageBuffer).toString('base64');
					const mimeType = imageFile.type;
					updateData.attachment = `data:${mimeType};base64,${base64Image}`;
					updateData.image_filename = imageFile.name;
				} catch (imageError) {
					console.error('Error processing image:', imageError);
					// Don't fail the entire request if image processing fails
				}
			}
			
			await database
				.update(announcements)
				.set(updateData)
				.where(eq(announcements.id, id));

			return { success: true, message: 'Announcement updated successfully!' };
		} catch (error) {
			console.error('Error updating announcement:', error);
			return fail(500, { error: 'Failed to update announcement.' });
		}
	},

	deleteAnnouncement: async ({ request }) => {
		const formData = await request.formData();
		const id = parseInt(formData.get('id') as string);

		if (!id) {
			return fail(400, { error: 'Announcement ID is required.' });
		}

		try {
			// Get database instance
			const database = await db();
			
			// Soft delete by setting is_active to 0
			await database
				.update(announcements)
				.set({ is_active: 0 })
				.where(eq(announcements.id, id));

			return { success: true, message: 'Announcement deleted successfully!' };
		} catch (error) {
			console.error('Error deleting announcement:', error);
			return fail(500, { error: 'Failed to delete announcement.' });
		}
	}
};
