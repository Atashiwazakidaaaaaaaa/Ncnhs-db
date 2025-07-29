import { db } from '$lib/server/db';
import { events } from '$lib/server/db/schema';
import { sql } from 'drizzle-orm';
import type { PageServerLoad } from './$types';

export const load: PageServerLoad = async ({ cookies }) => {
	const loggedIn = cookies.get('auth') === 'true';
	
	try {
		// Load existing events from the database
		const allEvents = await db.select().from(events);
		
		// Transform the data to match the frontend format
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

		return {
			events: transformedEvents,
			loggedIn
		};
	} catch (error) {
		console.error('Error loading events:', error);
		return {
			events: [],
			loggedIn
		};
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
