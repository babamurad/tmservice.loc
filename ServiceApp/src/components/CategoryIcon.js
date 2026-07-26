import { View, StyleSheet } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { colors } from '../theme';

// Бэкенд не хранит тип категории — сопоставляем по названию (RU/TM).
// Новая, ещё не описанная здесь категория просто получит нейтральную
// иконку по умолчанию, ничего не сломается.
const RULES = [
  { match: /сантех|santehnik/i, icon: 'water' },
  { match: /электр|elektrik/i, icon: 'flash' },
  { match: /авто|awtoremont/i, icon: 'car-sport' },
  { match: /мебел|ýygnamak|ygnamak/i, icon: 'hammer' },
];

function resolveIcon(category) {
  const name = `${category?.name_ru || ''} ${category?.name_tm || ''}`;
  const rule = RULES.find((r) => r.match.test(name));
  return rule?.icon || 'construct';
}

export default function CategoryIcon({ category, size = 22, tint = colors.primary }) {
  const badgeSize = size * 2.1;

  return (
    <View
      style={[
        styles.badge,
        {
          width: badgeSize,
          height: badgeSize,
          borderRadius: badgeSize / 2,
          backgroundColor: colors.primaryLight,
        },
      ]}
    >
      <Ionicons name={resolveIcon(category)} size={size} color={tint} />
    </View>
  );
}

const styles = StyleSheet.create({
  badge: { alignItems: 'center', justifyContent: 'center' },
});

export { resolveIcon };
