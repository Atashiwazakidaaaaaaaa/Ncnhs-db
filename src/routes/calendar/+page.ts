import type { PageServerLoad } from './$types';

export type PageData = {
	events: {
		id: number;
		date: string;
		title: string;
		type: string;
		time: string;
		location: string;
		description: string;
		organizer: string;
	}[];
};
