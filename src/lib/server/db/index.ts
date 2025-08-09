import { drizzle } from 'drizzle-orm/mysql2';
import mysql from 'mysql2/promise';
import * as schema from './schema';
import { env } from '$env/dynamic/private';

// Fallback to ncnhsdb if DATABASE_URL not set
const dbUrl = env.DATABASE_URL || 'mysql://root:root@localhost:3306/ncnhsdb';

let client: mysql.Connection | null = null;
let dbInstance: ReturnType<typeof drizzle> | null = null;

export async function db() {
  if (!dbInstance) {
    try {
      if (!client) {
        console.log('Connecting to database:', dbUrl);
        client = await mysql.createConnection(dbUrl);
      }
      dbInstance = drizzle(client, { schema, mode: 'default' });
    } catch (error) {
      console.error('Database connection failed:', error);
      throw error;
    }
  }
  return dbInstance;
}
