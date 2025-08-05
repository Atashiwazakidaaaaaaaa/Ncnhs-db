import { db } from '$lib/server/db';
import { events } from '$lib/server/db/schema';
import { json } from '@sveltejs/kit';
import { eq } from 'drizzle-orm';
import type { RequestHandler } from './$types';

export const PUT: RequestHandler = async ({ params, request }) => {
	try {
		const id = parseInt(params.id);
		const { title, description, event_date, start_time, end_time, location, event_type, organizer } = await request.json();

		if (!id || isNaN(id)) {
			return json({ error: 'Valid event ID is required' }, { status: 400 });
		}

		// Validate required fields
		if (!title || !event_date || !start_time || !end_time || !location || !organizer) {
			return json({ error: 'Missing required fields' }, { status: 400 });
		}

		// Update event
		const database = await db();
		await database.update(events).set({
			title,
			description: description || '',
			event_date: new Date(event_date),
			start_time,
			end_time,
			location,
			event_type: event_type || 'event',
			organizer
		}).where(eq(events.id, id));

		return json({ success: true, message: 'Event updated successfully' });
	} catch (error) {
		console.error('Error updating event:', error);
		return json({ error: 'Failed to update event' }, { status: 500 });
	}
};

export const DELETE: RequestHandler = async ({ params }) => {
	try {
		const id = parseInt(params.id);

		if (!id || isNaN(id)) {
			return json({ error: 'Valid event ID is required' }, { status: 400 });
		}

		const database = await db();
		await database.delete(events).where(eq(events.id, id));

		return json({ success: true, message: 'Event deleted successfully' });
	} catch (error) {
		console.error('Error deleting event:', error);
		return json({ error: 'Failed to delete event' }, { status: 500 });
	}
};
