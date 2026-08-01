import { View, TouchableOpacity, StyleSheet } from 'react-native';
import { colors, radius, spacing } from '../theme';

export default function Card({ children, onPress, style }) {
  const Wrapper = onPress ? TouchableOpacity : View;

  return (
    <Wrapper style={[styles.card, style]} onPress={onPress} activeOpacity={onPress ? 0.7 : 1}>
      {children}
    </Wrapper>
  );
}

const styles = StyleSheet.create({
  card: {
    backgroundColor: colors.surface,
    borderRadius: 20, // More friendly radius
    padding: spacing.lg,
    ...colors.shadows.small,
  },
});
