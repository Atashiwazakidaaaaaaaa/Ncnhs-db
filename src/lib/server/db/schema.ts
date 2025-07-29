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
	number: varchar('number', { length: 20 })
});

export const events = mysqlTable('events', {
	id: serial('id').primaryKey(),
	title: varchar('title', { length: 255 }).notNull(),
	description: text('description'),
	event_date: date('event_date').notNull(),
	start_time: time('start_time').notNull(),
	end_time: time('end_time').notNull(),
	location: varchar('location', { length: 255 }).notNull(),
	event_type: varchar('event_type', { length: 50 }).notNull().default('event'), // 'meeting', 'event', 'academic'
	organizer: varchar('organizer', { length: 255 }).notNull(),
	created_at: timestamp('created_at').defaultNow(),
	updated_at: timestamp('updated_at').defaultNow()
});

export type Faculty = InferSelectModel<typeof faculty>;
export type Event = InferSelectModel<typeof events>;
