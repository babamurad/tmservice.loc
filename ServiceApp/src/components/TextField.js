import { View, Text, TextInput, StyleSheet } from 'react-native';
import { colors, radius, spacing, typography } from '../theme';

export default function TextField({ label, style, ...inputProps }) {
  return (
    <View style={styles.wrapper}>
      {label && <Text style={[typography.caption, styles.label]}>{label}</Text>}
      <TextInput
        style={[styles.input, style]}
        placeholderTextColor={colors.inkFaint}
        {...inputProps}
      />
    </View>
  );
}

const styles = StyleSheet.create({
  wrapper: { marginBottom: spacing.md },
  label: { marginBottom: spacing.xs },
  input: {
    borderWidth: 1,
    borderColor: colors.border,
    borderRadius: radius.md,
    paddingHorizontal: spacing.lg,
    paddingVertical: 13,
    fontSize: 15,
    color: colors.ink,
    backgroundColor: colors.surface,
  },
});
