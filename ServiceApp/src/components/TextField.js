import { useState } from 'react';
import { View, Text, TextInput, StyleSheet } from 'react-native';
import { colors, radius, spacing, typography } from '../theme';

export default function TextField({ label, style, ...inputProps }) {
  const [isFocused, setIsFocused] = useState(false);

  return (
    <View style={styles.wrapper}>
      {label && <Text style={[typography.caption, styles.label]}>{label}</Text>}
      <TextInput
        style={[
          styles.input,
          style,
          isFocused && styles.inputFocused,
        ]}
        placeholderTextColor={colors.inkFaint}
        onFocus={() => setIsFocused(true)}
        onBlur={() => setIsFocused(false)}
        {...inputProps}
      />
    </View>
  );
}

const styles = StyleSheet.create({
  wrapper: { marginBottom: spacing.md },
  label: { marginBottom: spacing.xs, marginLeft: 4 },
  input: {
    borderWidth: 1,
    borderColor: colors.border,
    borderRadius: 16, // Softer rounding
    paddingHorizontal: spacing.lg,
    paddingVertical: 14, // Taller inputs
    fontSize: 16,
    color: colors.ink,
    backgroundColor: '#FAF7F5', // Slightly different background
  },
  inputFocused: {
    borderColor: colors.primary,
    backgroundColor: colors.surface,
  },
});
