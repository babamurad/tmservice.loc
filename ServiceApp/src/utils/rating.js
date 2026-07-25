export function renderStars(rating) {
  const filled = Math.round(rating);

  return '★'.repeat(filled) + '☆'.repeat(5 - filled);
}
