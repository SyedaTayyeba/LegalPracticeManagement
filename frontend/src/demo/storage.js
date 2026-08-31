const DEMO_PREFIX = 'legal_demo_'

export function getDemoData(key, fallback = []) {
  try {
    const stored = localStorage.getItem(`${DEMO_PREFIX}${key}`)

    if (!stored) {
      localStorage.setItem(
        `${DEMO_PREFIX}${key}`,
        JSON.stringify(fallback)
      )

      return fallback
    }

    return JSON.parse(stored)
  } catch (error) {
    console.error(`Failed to read demo data: ${key}`, error)
    return fallback
  }
}

export function setDemoData(key, data) {
  localStorage.setItem(
    `${DEMO_PREFIX}${key}`,
    JSON.stringify(data)
  )

  return data
}

export function clearDemoData(key) {
  localStorage.removeItem(`${DEMO_PREFIX}${key}`)
}