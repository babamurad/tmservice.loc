import { View, Text, StyleSheet } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { colors, spacing, typography } from '../theme';

export default function EmptyState({ icon = 'search-outline', title, subtitle }) {
  return (
    <View style={styles.container}>
      <Ionicons name={icon} size={40} color={colors.inkFaint} />
      <Text style={[typography.heading, styles.title]}>{title}</Text>
      {subtitle && <Text style={[typography.bodyMuted, styles.subtitle]}>{subtitle}</Text>}
    </View>
  );
}

const styles = StyleSheet.create({
  container: { alignItems: 'center', justifyContent: 'center', padding: spacing.xxl },
  title: { marginTop: spacing.md, textAlign: 'center' },
  subtitle: { marginTop: spacing.xs, textAlign: 'center' },
});
