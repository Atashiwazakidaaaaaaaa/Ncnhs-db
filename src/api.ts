const BASE_URL ='http://localhost/back-ends';
export async function fetchData (endpoint: string) {
  const res = await fetch (`${BASE_URL}/${endpoint}`);
  const data= await res.json();
  return data;
}
