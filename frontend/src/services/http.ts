const baseUrl = import.meta.env.VITE_API_URL ?? 'http://localhost:8080'

/**
 * Error thrown for non-2xx API responses. Carries the backend `message` and
 * the HTTP status so callers can react (e.g. treat 422 as "not available yet").
 */
export class ApiError extends Error {
  readonly status: number

  constructor(message: string, status: number) {
    super(message)
    this.name = 'ApiError'
    this.status = status
  }
}

function extractMessage(body: unknown): string | null {
  if (body && typeof body === 'object' && 'message' in body) {
    const message = (body as { message: unknown }).message
    return typeof message === 'string' ? message : null
  }

  return null
}

/**
 * Thin JSON fetch wrapper for the backend API. Prefixes `/api`, sets JSON
 * headers, and normalises errors into {@link ApiError}.
 */
export async function request<T>(path: string, init: RequestInit = {}): Promise<T> {
  const response = await fetch(`${baseUrl}/api${path}`, {
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      ...init.headers,
    },
    ...init,
  })

  const text = await response.text()
  const body: unknown = text ? JSON.parse(text) : null

  if (!response.ok) {
    throw new ApiError(
      extractMessage(body) ?? `Request failed (${response.status})`,
      response.status,
    )
  }

  return body as T
}
