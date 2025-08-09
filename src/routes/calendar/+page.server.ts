import { db } from '$lib/server/db';
import { events } from '$lib/server/db/schema';
import { sql } from 'drizzle-orm';
import type { PageServerLoad } from './$types';

export const load: PageServerLoad = async ({ cookies }) => {
	const loggedIn = cookies.get('auth') === 'true';
	
	try {
		// Load existing events from the database
		const database = await db();
		const allEvents = await database.select().from(events);
		
		console.log('Calendar server: Found', allEvents.length, 'events in database');
		if (allEvents.length > 0) {
			console.log('Sample event:', allEvents[0]);
		}
		
		// Transform the data to match the frontend format
		const transformedEvents = allEvents.map(event => {
			const raw = event.event_date as unknown as string | Date;
			let dateStr: string;
			if (raw instanceof Date) {
				dateStr = raw.toISOString().split('T')[0];
			} else {
				const s = String(raw);
				// Handle possible 'YYYY-MM-DDTHH:MM:SS' form
				dateStr = s.includes('T') ? s.split('T')[0] : s;
			}
			const transformed = {
				id: event.id,
				date: dateStr,
				title: event.title,
				type: event.event_type,
				time: formatTime(event.start_time, event.end_time),
				location: event.location,
				description: event.description || '',
				organizer: event.organizer
			};
			console.log('Transformed event:', transformed);
			return transformed;
		});

		console.log('Calendar server: Returning', transformedEvents.length, 'transformed events');
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
