// "Халы" — палитра отсылает к туркменскому ковроткачеству (гёль на флаге,
// глубокий винно-красный цвет туркменских ковров), а не к дефолтному
// iOS-синему и не к безопасному "оранжевый + крем", в который скатывается
// почти любой "тёплый" интерфейс. Зелёный оставлен только как статус
// "свободен" — намеренно не соседствует с красным как пара цветов,
// чтобы не читаться как копия флага.
export const colors = {
  primary: '#A6283A',
  primaryDark: '#7D1E2C',
  primaryLight: '#F5E3E5',

  ink: '#241B1B',
  inkMuted: '#8A7876',
  inkFaint: '#BBA9A6',

  success: '#1E8449',
  successLight: '#E1F3E8',
  danger: '#C2410C',
  dangerLight: '#FBE7DB',

  rating: '#C98A1E',
  ratingMuted: '#E9DCC5',

  bg: '#F6F1EE',
  surface: '#FFFFFF',
  border: '#E7DDD8',

  overlay: 'rgba(36, 27, 27, 0.55)',
  white: '#FFFFFF',

  // Soft Shadows for UI depth
  shadows: {
    small: {
      shadowColor: '#A6283A',
      shadowOffset: { width: 0, height: 2 },
      shadowOpacity: 0.05,
      shadowRadius: 4,
      elevation: 2,
    },
    medium: {
      shadowColor: '#A6283A',
      shadowOffset: { width: 0, height: 4 },
      shadowOpacity: 0.1,
      shadowRadius: 8,
      elevation: 4,
    },
    large: {
      shadowColor: '#A6283A',
      shadowOffset: { width: 0, height: 8 },
      shadowOpacity: 0.15,
      shadowRadius: 16,
      elevation: 8,
    }
  }
};
