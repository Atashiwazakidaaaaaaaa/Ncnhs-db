import { drizzle } from 'drizzle-orm/mysql2';
import mysql from 'mysql2/promise';
import * as schema from './schema';
import { env } from '$env/dynamic/private';

if (!env.DATABASE_URL) throw new Error('DATABASE_URL is not set');

let client: mysql.Connection | null = null;
let dbInstance: ReturnType<typeof drizzle> | null = null;

export async function db() {
  if (!dbInstance) {
    try {
      if (!client) {
        client = await mysql.createConnection(env.DATABASE_URL);
      }
      dbInstance = drizzle(client, { schema, mode: 'default' });
    } catch (error) {
      console.error('Database connection failed:', error);
      throw error;
    }
  }
  return dbInstance;
}
