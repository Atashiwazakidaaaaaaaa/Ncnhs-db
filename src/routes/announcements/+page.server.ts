import { fail } from '@sveltejs/kit';
import type { Actions, PageServerLoad } from './$types';
import { db } from '$lib/server/db';
import { announcements } from '$lib/server/db/schema';
import { eq, desc } from 'drizzle-orm';

export const load: PageServerLoad = async () => {
	try {
		const database = await db();
		const allAnnouncements = await database
			.select()
			.from(announcements)    
			.where(eq(announcements.is_active, 1))
			.orderBy(desc(announcements.display_date));

		return {
			announcements: allAnnouncements.map(a => ({
                ...a,
                date_posted: a.date_posted ? new Date(a.date_posted).toISOString().split('T')[0] : null,
                display_date: a.display_date ? new Date(a.display_date).toISOString().split('T')[0] : null,
                created_at: a.created_at ? new Date(a.created_at).toISOString() : null,
                updated_at: a.updated_at ? new Date(a.updated_at).toISOString() : null,
            }))
		};
	} catch (error) {
		console.error('Error loading announcements:', error);
		return {
			announcements: [],
			error: error instanceof Error ? error.message : 'Failed to load announcements'
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
		const author = 'Administrator';
		const imageFile = formData.get('image') as File;

		if (!title || !content || !displayDate) {
			return fail(400, { action: 'createAnnouncement', error: 'Title, content, and display date are required.' });
		}

		try {
			const database = await db();
			
			let imageData = null;
			let imageFilename = null;
			if (imageFile && imageFile.size > 0) {
				try {
					const imageBuffer = await imageFile.arrayBuffer();
					const base64Image = Buffer.from(imageBuffer).toString('base64');
					const mimeType = imageFile.type;
					imageData = `data:${mimeType};base64,${base64Image}`;
					imageFilename = imageFile.name;
				} catch (imageError) {
					console.error('Error processing image:', imageError);
          			return fail(500, { action: 'createAnnouncement', error: 'There was an error processing the image.' });
				}
			}
			
			const announcementData = {
				title,
				content,
				date_posted: new Date(),
				display_date: new Date(displayDate),
				category,
				author,
				attachment: imageData,
				image_filename: imageFilename,
				is_active: 1
			};
			
			await database.insert(announcements).values(announcementData as any);
			
			return { 
				action: 'createAnnouncement',
				success: true, 
				message: 'Announcement created successfully!' 
			};
		} catch (error) {
			console.error('Error creating announcement:', error);
			return fail(500, { action: 'createAnnouncement', error: 'A database error occurred.' });
		}
	},

	deleteAnnouncement: async ({ request }) => {
		const formData = await request.formData();
		const id = parseInt(formData.get('id') as string);
		
		if (!id) {
			return fail(400, { action: 'deleteAnnouncement', error: 'Announcement ID is required.' });
		}

		try {
			const database = await db();
			const result = await database
				.update(announcements)
				.set({ is_active: 0, updated_at: new Date() })
				.where(eq(announcements.id, id));
      
            if (result[0].affectedRows === 0) {
                return fail(404, { action: 'deleteAnnouncement', error: 'Announcement not found or already deleted.' });
            }

			return { action: 'deleteAnnouncement', success: true, message: 'Announcement deleted successfully!' };
		} catch (error) {
			console.error('Error deleting announcement:', error);
			return fail(500, { action: 'deleteAnnouncement', error: 'A server error occurred.' });
		}
	}
};