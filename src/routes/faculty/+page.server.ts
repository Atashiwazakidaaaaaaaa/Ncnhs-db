import { db } from '$lib/server/db';
import { faculty } from '$lib/server/db/schema';
import type { PageServerLoad } from './$types';

// Fallback data for when database is not available
const fallbackFaculty = [
  { id: 1, name: "N/A", role: "Principal", department: "Administration", email: null, number: null },
  { id: 2, name: "N/A", role: "Assistant Principal", department: "Administration", email: null, number: null },
  { id: 3, name: "N/A", role: "Master Teacher", department: "Mathematics", email: null, number: null },
  { id: 4, name: "N/A", role: "Master Teacher", department: "Science", email: null, number: null },
  { id: 5, name: "N/A", role: "Master Teacher", department: "English", email: null, number: null },
  { id: 6, name: "N/A", role: "Master Teacher", department: "Filipino", email: null, number: null },
  { id: 7, name: "N/A", role: "Master Teacher", department: "Social Studies", email: null, number: null },
  { id: 8, name: "N/A", role: "Master Teacher", department: "MAPEH", email: null, number: null },
  { id: 9, name: "N/A", role: "Master Teacher", department: "TLE", email: null, number: null },
  { id: 10, name: "N/A", role: "Teacher", department: "Mathematics", email: null, number: null },
  { id: 11, name: "N/A", role: "Teacher", department: "Mathematics", email: null, number: null },
  { id: 12, name: "N/A", role: "Teacher", department: "Mathematics", email: null, number: null },
  { id: 13, name: "N/A", role: "Teacher", department: "Science", email: null, number: null },
  { id: 14, name: "N/A", role: "Teacher", department: "Science", email: null, number: null },
  { id: 15, name: "N/A", role: "Laboratory Technician", department: "Science", email: null, number: null },
  { id: 16, name: "N/A", role: "Teacher", department: "English", email: null, number: null },
  { id: 17, name: "N/A", role: "Teacher", department: "English", email: null, number: null },
  { id: 18, name: "N/A", role: "Teacher", department: "Filipino", email: null, number: null },
  { id: 19, name: "N/A", role: "Teacher", department: "Filipino", email: null, number: null },
  { id: 20, name: "N/A", role: "Teacher", department: "Social Studies", email: null, number: null },
  { id: 21, name: "N/A", role: "Teacher", department: "MAPEH", email: null, number: null },
  { id: 22, name: "N/A", role: "Teacher", department: "MAPEH", email: null, number: null },
  { id: 23, name: "N/A", role: "Teacher", department: "TLE", email: null, number: null },
  { id: 24, name: "N/A", role: "Teacher", department: "TLE", email: null, number: null }
];

export const load: PageServerLoad = async ({ cookies }) => {
  const loggedIn = cookies.get('auth') === 'true';

  try {
    const allFaculty = await db.select().from(faculty);
    return {
      faculty: allFaculty,
      loggedIn
    };
  } catch (error) {
    console.error('Database connection failed, using fallback data:', error);
    return {
      faculty: fallbackFaculty,
      loggedIn
    };
  }
};