const baseUrl = import.meta.env.VITE_API_URL ?? 'http://localhost:8080'

/**
 * Thin fetch wrapper for the backend API. Endpoints are added as the
 * application grows; for now it only centralises the base URL and JSON
 * handling so callers stay consistent.
 */
export async function request<T>(path: string, init: RequestInit = {}): Promise<T> {
  const response = await fetch(`${baseUrl}${path}`, {
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      ...init.headers,
    },
    ...init,
  })

  if (!response.ok) {
    throw new Error(`Request failed: ${response.status} ${response.statusText}`)
  }

  return response.json() as Promise<T>
}
