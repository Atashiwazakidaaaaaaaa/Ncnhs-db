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
			.orderBy(desc(announcements.created_at));

		return {
			announcements: allAnnouncements
		};
	} catch (error) {
		console.error('Error loading announcements:', error);
		return {
			announcements: [],
			error: 'Failed to load announcements'
		};
	}
};

export const actions: Actions = {
	createAnnouncement: async ({ request }) => {
		try {
			const database = await db();
			const formData = await request.formData();
			
			const title = formData.get('title') as string;
			const display_date = formData.get('display_date') as string;
			const content = formData.get('content') as string;
			const category = formData.get('category') as string;
			const imageFile = formData.get('image') as File;
			
			let imageData: string | null = null;
			let filename: string | null = null;
			
			if (imageFile && imageFile.size > 0) {
				const arrayBuffer = await imageFile.arrayBuffer();
				const buffer = Buffer.from(arrayBuffer);
				imageData = `data:${imageFile.type};base64,${buffer.toString('base64')}`;
				filename = imageFile.name;
			}
			
			const result = await database.insert(announcements).values({
				title,
				content,
				date_posted: new Date().toISOString().split('T')[0] as any,
				display_date,
				category,
				attachment: imageData,
				image_filename: filename,
				author: 'Administrator',
				is_active: 1
			} as any);
			
			return { success: true, message: 'Announcement created successfully' };
		} catch (error) {
			console.error('Create announcement error:', error);
			return fail(500, { error: 'Failed to create announcement' });
		}
	},

	updateAnnouncement: async ({ request }) => {
		try {
			const database = await db();
			const formData = await request.formData();
			
			const id = parseInt(formData.get('id') as string);
			const title = formData.get('title') as string;
			const display_date = formData.get('display_date') as string;
			const content = formData.get('content') as string;
			
			const updateData: any = {};
			if (title) updateData.title = title;
			if (display_date) updateData.display_date = display_date;
			if (content) updateData.content = content;
			updateData.updated_at = new Date();
			
			await database
				.update(announcements)
				.set(updateData)
				.where(eq(announcements.id, id));
			
			return { success: true, message: 'Announcement updated successfully' };
		} catch (error) {
			console.error('Update announcement error:', error);
			return fail(500, { error: 'Failed to update announcement' });
		}
	},

	deleteAnnouncement: async ({ request }) => {
		try {
			const database = await db();
			const formData = await request.formData();
			
			const id = parseInt(formData.get('id') as string);
			
			await database
				.update(announcements)
				.set({ is_active: 0 })
				.where(eq(announcements.id, id));
			
			return { success: true, message: 'Announcement deleted successfully' };
		} catch (error) {
			console.error('Delete announcement error:', error);
			return fail(500, { error: 'Failed to delete announcement' });
		}
	}
};