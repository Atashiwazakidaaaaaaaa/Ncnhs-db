import { json } from '@sveltejs/kit';
import { db } from '$lib/server/db/index.js';
import { faculty } from '$lib/server/db/schema.js';
import { eq } from 'drizzle-orm';
import type { RequestHandler } from './$types';

// GET faculty by id
export const GET: RequestHandler = async ({ params }) => {
  try {
    const id = parseInt(params.id);
    const database = await db();
    const singleFaculty = await database.select().from(faculty).where(eq(faculty.id, id));

    if (singleFaculty.length === 0) {
      return json({ error: 'Faculty not found' }, { status: 404 });
    }

    return json(singleFaculty[0]);
  } catch (error) {
    console.error('Error fetching faculty:', error);
    return json({ error: 'Failed to fetch faculty' }, { status: 500 });
  }
};

// PUT update faculty
export const PUT: RequestHandler = async ({ params, request }) => {
  try {
    const id = parseInt(params.id);
    const { name, role, department, email, number, image_url } = await request.json();
    
    if (!name || !role || !department) {
      return json({ error: 'Name, role, and department are required' }, { status: 400 });
    }
    
    const database = await db();
    await database.update(faculty)
      .set({ 
        name, 
        role, 
        department,
        email: email || null,
        number: number || null,
        image_url: image_url || null
      })
      .where(eq(faculty.id, id));
    
    // Get the updated faculty
    const updatedFaculty = await database.select().from(faculty).where(eq(faculty.id, id));
    
    return json({ faculty: updatedFaculty[0] });
  } catch (error) {
    console.error('Error updating faculty:', error);
    return json({ error: 'Failed to update faculty' }, { status: 500 });
  }
};

// DELETE faculty
export const DELETE: RequestHandler = async ({ params }) => {
  try {
    const id = parseInt(params.id);
    
    const database = await db();
    await database.delete(faculty).where(eq(faculty.id, id));
    
    return json({ success: true });
  } catch (error) {
    console.error('Error deleting faculty:', error);
    return json({ error: 'Failed to delete faculty' }, { status: 500 });
  }
};
