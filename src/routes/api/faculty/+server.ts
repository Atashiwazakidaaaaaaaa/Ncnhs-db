import { db } from '$lib/server/db/index.js';
import { faculty } from '$lib/server/db/schema.js';
import { json } from '@sveltejs/kit';
import { eq, desc } from 'drizzle-orm';
import type { RequestHandler } from './$types';

// GET all faculty
export const GET: RequestHandler = async () => {
  try {
    const allFaculty = await db.select().from(faculty);
    return json(allFaculty);
  } catch (error) {
    console.error('Error fetching faculty:', error);
    return json({ error: 'Failed to fetch faculty' }, { status: 500 });
  }
};

// POST new faculty
export const POST: RequestHandler = async ({ request }) => {
  try {
    const { name, role, department, email, number } = await request.json();
    
    if (!name || !role || !department) {
      return json({ error: 'Name, role, and department are required' }, { status: 400 });
    }
    
    await db.insert(faculty).values({
      name,
      role,
      department,
      email: email || null,
      number: number || null
    });
    
    // Get the most recently inserted faculty
    const allFaculty = await db.select().from(faculty);
    const newFaculty = allFaculty[allFaculty.length - 1];
    
    return json({ faculty: newFaculty }, { status: 201 });
  } catch (error) {
    console.error('Error creating faculty:', error);
    return json({ error: 'Failed to create faculty' }, { status: 500 });
  }
};