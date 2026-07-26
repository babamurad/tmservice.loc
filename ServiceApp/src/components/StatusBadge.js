import { View, Text, StyleSheet } from 'react-native';
import { colors, radius, spacing } from '../theme';

export default function StatusBadge({ isFree, compact = false }) {
  const tint = isFree ? colors.success : colors.danger;
  const bg = isFree ? colors.successLight : colors.dangerLight;
  const label = isFree ? 'Свободен' : 'Занят';

  if (compact) {
    return <View style={[styles.dot, { backgroundColor: tint }]} />;
  }

  return (
    <View style={[styles.badge, { backgroundColor: bg }]}>
      <View style={[styles.dot, { backgroundColor: tint }]} />
      <Text style={[styles.label, { color: tint }]}>{label}</Text>
    </View>
  );
}

const styles = StyleSheet.create({
  badge: {
    flexDirection: 'row',
    alignItems: 'center',
    alignSelf: 'flex-start',
    borderRadius: radius.pill,
    paddingHorizontal: spacing.md,
    paddingVertical: 5,
    gap: spacing.xs,
  },
  dot: { width: 9, height: 9, borderRadius: 5 },
  label: { fontSize: 12.5, fontWeight: '700' },
});
