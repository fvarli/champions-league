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
 * Friendly message for HTTP 429. If the backend exposes a Retry-After header
 * (readable same-origin), a short wait hint is included; otherwise it degrades
 * to a generic "wait a moment" message.
 */
function rateLimitMessage(response: Response): string {
  const retryAfter = Number.parseInt(response.headers.get('Retry-After') ?? '', 10)
  const wait = Number.isFinite(retryAfter) && retryAfter > 0 ? `about ${retryAfter}s` : 'a moment'

  return `Too many actions in a short time. Please wait ${wait} and try again.`
}

/**
 * Thin JSON fetch wrapper for the backend API. Prefixes the versioned `/api/v1`
 * base, sets JSON headers, and normalises errors into {@link ApiError}.
 */
export async function request<T>(path: string, init: RequestInit = {}): Promise<T> {
  const response = await fetch(`${baseUrl}/api/v1${path}`, {
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
    if (response.status === 429) {
      throw new ApiError(rateLimitMessage(response), 429)
    }

    throw new ApiError(
      extractMessage(body) ?? `Request failed (${response.status})`,
      response.status,
    )
  }

  return body as T
}
