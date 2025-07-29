import { db } from '$lib/server/db';
import { events } from '$lib/server/db/schema';
import { json } from '@sveltejs/kit';
import { eq } from 'drizzle-orm';
import type { RequestHandler } from './$types';

export const GET: RequestHandler = async () => {
	try {
		const allEvents = await db.select().from(events);
		
		const transformedEvents = allEvents.map(event => ({
			id: event.id,
			date: event.event_date instanceof Date ? event.event_date.toISOString().split('T')[0] : event.event_date,
			title: event.title,
			type: event.event_type,
			time: formatTime(event.start_time, event.end_time),
			location: event.location,
			description: event.description || '',
			organizer: event.organizer
		}));

		return json({ events: transformedEvents });
	} catch (error) {
		console.error('Error fetching events:', error);
		return json({ error: 'Failed to fetch events' }, { status: 500 });
	}
};

export const POST: RequestHandler = async ({ request }) => {
	try {
		const { title, description, event_date, start_time, end_time, location, event_type, organizer } = await request.json();

		// Validate required fields
		if (!title || !event_date || !start_time || !end_time || !location || !organizer) {
			return json({ error: 'Missing required fields' }, { status: 400 });
		}

		// Insert new event
		const result = await db.insert(events).values({
			title,
			description: description || '',
			event_date: new Date(event_date),
			start_time,
			end_time,
			location,
			event_type: event_type || 'event',
			organizer
		});

		return json({ success: true, message: 'Event created successfully' });
	} catch (error) {
		console.error('Error creating event:', error);
		return json({ error: 'Failed to create event' }, { status: 500 });
	}
};

export const PUT: RequestHandler = async ({ request }) => {
	try {
		const { id, title, description, event_date, start_time, end_time, location, event_type, organizer } = await request.json();

		if (!id) {
			return json({ error: 'Event ID is required' }, { status: 400 });
		}

		// Update event
		await db.update(events).set({
			title,
			description,
			event_date: new Date(event_date),
			start_time,
			end_time,
			location,
			event_type,
			organizer
		}).where(eq(events.id, id));

		return json({ success: true });
	} catch (error) {
		console.error('Error updating event:', error);
		return json({ error: 'Failed to update event' }, { status: 500 });
	}
};

export const DELETE: RequestHandler = async ({ request }) => {
	try {
		const { id } = await request.json();

		if (!id) {
			return json({ error: 'Event ID is required' }, { status: 400 });
		}

		await db.delete(events).where(eq(events.id, id));

		return json({ success: true });
	} catch (error) {
		console.error('Error deleting event:', error);
		return json({ error: 'Failed to delete event' }, { status: 500 });
	}
};

function formatTime(startTime: string, endTime: string): string {
	const formatTimeString = (time: string) => {
		const [hours, minutes] = time.split(':');
		const hour = parseInt(hours);
		const ampm = hour >= 12 ? 'PM' : 'AM';
		const hour12 = hour % 12 || 12;
		return `${hour12}:${minutes} ${ampm}`;
	};

	return `${formatTimeString(startTime)} - ${formatTimeString(endTime)}`;
}
