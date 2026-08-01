import { View, Text, StyleSheet } from 'react-native';
import { colors, radius, spacing } from '../theme';

export default function StatusBadge({ isFree, compact = false }) {
  const tint = isFree ? '#1E8449' : colors.danger;
  const bg = isFree ? '#E1F3E8' : colors.dangerLight;
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
    borderRadius: 20,
    paddingHorizontal: spacing.md,
    paddingVertical: 6,
    gap: spacing.xs,
  },
  dot: { width: 8, height: 8, borderRadius: 4 },
  label: { fontSize: 13, fontWeight: '700' },
});
