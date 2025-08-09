import { json } from '@sveltejs/kit';
import type { RequestHandler } from './$types';
import fs from 'fs/promises';
import path from 'path';

export const POST: RequestHandler = async ({ request }) => {
  try {
    const formData = await request.formData();
    const image = formData.get('image') as File;

    if (!image) {
      return json({ error: 'No image provided' }, { status: 400 });
    }

    const uploadsDir = path.join(process.cwd(), 'static', 'uploads', 'faculty');
    await fs.mkdir(uploadsDir, { recursive: true });

    const imageName = `${Date.now()}-${image.name}`;
    const imagePath = path.join(uploadsDir, imageName);

    await fs.writeFile(imagePath, Buffer.from(await image.arrayBuffer()));

    return json({ imageUrl: imageName });
  } catch (error) {
    console.error('Error uploading image:', error);
    return json({ error: 'Failed to upload image' }, { status: 500 });
  }
};