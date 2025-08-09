import { mysqlTable, serial, int, varchar, text, date, time, timestamp } from 'drizzle-orm/mysql-core';
import type { InferSelectModel } from 'drizzle-orm';

export const usercreds = mysqlTable('usercreds', {
	uid: int('uid').primaryKey(),
	username: varchar('username', { length: 255 }).notNull(),
	password: varchar('password', { length: 255 }).notNull()
});

export const faculty = mysqlTable('faculty', {
	id: serial('id').primaryKey(),
	name: varchar('name', { length: 255 }).notNull(),
	role: varchar('role', { length: 255 }).notNull(),
	department: varchar('department', { length: 255 }).notNull(),
	email: varchar('email', { length: 255 }),
	number: varchar('number', { length: 20 }),
	image_url: varchar('image_url', { length: 500 })
});

export const events = mysqlTable('events', {
	id: serial('id').primaryKey(),
	title: varchar('title', { length: 255 }).notNull(),
	description: text('description'),
	event_date: date('event_date').notNull(),
	start_time: time('start_time').notNull(),
	end_time: time('end_time').notNull(),
	location: varchar('location', { length: 255 }).notNull(),
	event_type: varchar('event_type', { length: 50 }).notNull().default('event'), // 'meeting', 'event', 'academic', 'announcement'
	organizer: varchar('organizer', { length: 255 }).notNull(),
	announcement_id: int('announcement_id'), // Reference to the announcement that created this event
	created_at: timestamp('created_at').defaultNow(),
	updated_at: timestamp('updated_at').defaultNow()
});

export const schoolyr = mysqlTable('schoolyr', {
	schoolyr: varchar('schoolyr', { length: 255 }).notNull().primaryKey()
});

export const announcements = mysqlTable('announcements', {
	id: serial('id').primaryKey(),
	title: varchar('title', { length: 255 }).notNull(),
	content: text('content').notNull(),
	date_posted: date('date_posted').notNull(),
	display_date: varchar('display_date', { length: 100 }).notNull(), // Human-readable date like "August 5th, 2025"
	category: varchar('category', { length: 50 }).notNull().default('general'), // 'academic', 'event', 'maintenance', 'general'
	priority: varchar('priority', { length: 20 }).notNull().default('medium'), // 'high', 'medium', 'low'
	attachment: text('attachment'), // Base64 encoded image data
	image_filename: varchar('image_filename', { length: 255 }), // Original filename for reference
	author: varchar('author', { length: 255 }).notNull().default('Administrator'),
	is_active: int('is_active').notNull().default(1), // 1 for active, 0 for archived
	// Event integration fields
	event_date: date('event_date'), // Date when the announced event will occur
	event_time_start: time('event_time_start'), // Start time of the announced event
	event_time_end: time('event_time_end'), // End time of the announced event
	event_location: varchar('event_location', { length: 255 }), // Location of the announced event
	create_calendar_event: int('create_calendar_event').default(0), // Whether to create a corresponding calendar event
	calendar_event_id: int('calendar_event_id'), // Reference to the created calendar event
	created_at: timestamp('created_at').defaultNow(),
	updated_at: timestamp('updated_at').defaultNow()
});

export const about = mysqlTable('about', {
	id: serial('id').primaryKey(),
	section_name: varchar('section_name', { length: 100 }).notNull().unique(),
	Info: text('Info').notNull(),
	created_at: timestamp('created_at').defaultNow(),
	updated_at: timestamp('updated_at').defaultNow()
});

export type Faculty = InferSelectModel<typeof faculty>;
export type Event = InferSelectModel<typeof events>;
export type SchoolYear = InferSelectModel<typeof schoolyr>;
export type Announcement = InferSelectModel<typeof announcements>;
export type About = InferSelectModel<typeof about>;
