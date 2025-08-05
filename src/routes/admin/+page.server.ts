import { fail } from '@sveltejs/kit';
import type { Actions } from './$types';
import { db } from '$lib/server/db';
import { announcements } from '$lib/server/db/schema';
import { eq } from 'drizzle-orm';
import fs from 'fs/promises';
import path from 'path';

const UPLOAD_DIR = path.resolve('static/uploads');

// Ensure the upload directory exists
const ensureUploadDir = async () => {
	try {
		await fs.mkdir(UPLOAD_DIR, { recursive: true });
	} catch (error) {
		console.error('Error creating upload directory:', error);
	}
};

ensureUploadDir();

export const actions: Actions = {
	uploadImage: async ({ request }) => {
		const formData = await request.formData();
		const file = formData.get('attachment') as File;
		const announcementId = formData.get('announcementId') as string;

		if (!file) {
			return fail(400, { error: 'No file was uploaded.' });
		}

		if (!announcementId) {
			return fail(400, { error: 'Announcement ID is missing.' });
		}

		try {
			const fileExtension = path.extname(file.name);
			const newFileName = `${announcementId}${fileExtension}`;
			const filePath = path.join(UPLOAD_DIR, newFileName);

			// Save the file to the server
			await fs.writeFile(filePath, Buffer.from(await file.arrayBuffer()));

			const fileUrl = `/uploads/${newFileName}`;

			// Update the announcement in the database
			await db
				.update(announcements)
				.set({ attachment: fileUrl })
				.where(eq(announcements.id, announcementId));

			return { success: true, filePath: fileUrl };
		} catch (error) {
			console.error('Error uploading file:', error);
			return fail(500, { error: 'Failed to upload file.' });
		}
	}
};