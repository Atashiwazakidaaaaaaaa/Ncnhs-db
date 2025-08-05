import { db } from '$lib/server/db';
import { schoolyr } from '$lib/server/db/schema';
import { json } from '@sveltejs/kit';
import { eq } from 'drizzle-orm';
import type { RequestHandler } from './$types';

// GET current school year
export const GET: RequestHandler = async () => {
	try {
		const database = await db();
		const currentSchoolYear = await database.select().from(schoolyr).limit(1);
		
		if (currentSchoolYear.length > 0) {
			return json({ schoolYear: currentSchoolYear[0].schoolyr });
		} else {
			// Return default if no school year is set
			return json({ schoolYear: 'School Year 2024-2025' });
		}
	} catch (error) {
		console.error('Error fetching school year:', error);
		return json({ error: 'Failed to fetch school year' }, { status: 500 });
	}
};

// POST/PUT update school year
export const POST: RequestHandler = async ({ request }) => {
	try {
		const { schoolYear } = await request.json();
		
		if (!schoolYear || !schoolYear.trim()) {
			return json({ error: 'School year is required' }, { status: 400 });
		}
		
		const database = await db();
		// Check if a school year record exists
		const existingSchoolYear = await database.select().from(schoolyr).limit(1);
		
		if (existingSchoolYear.length > 0) {
			// Update existing record
			await database.update(schoolyr)
				.set({ schoolyr: schoolYear.trim() })
				.where(eq(schoolyr.schoolyr, existingSchoolYear[0].schoolyr));
		} else {
			// Insert new record
			await database.insert(schoolyr).values({
				schoolyr: schoolYear.trim()
			});
		}
		
		return json({ success: true, message: 'School year updated successfully' });
	} catch (error) {
		console.error('Error updating school year:', error);
		return json({ error: 'Failed to update school year' }, { status: 500 });
	}
};
