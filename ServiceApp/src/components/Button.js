import { Animated, Pressable, Text, ActivityIndicator, StyleSheet } from 'react-native';
import { useRef } from 'react';
import { Ionicons } from '@expo/vector-icons';
import { colors, radius, spacing, typography } from '../theme';

const VARIANTS = {
  primary: { bg: colors.primary, border: colors.primary, text: colors.white },
  danger: { bg: colors.danger, border: colors.danger, text: colors.white },
  success: { bg: '#1E8449', border: '#1E8449', text: colors.white },
  dark: { bg: colors.ink, border: colors.ink, text: colors.white },
  outline: { bg: 'transparent', border: colors.border, text: colors.ink },
};

export default function Button({
  title,
  onPress,
  variant = 'primary',
  icon,
  loading = false,
  disabled = false,
  style,
}) {
  const v = VARIANTS[variant] || VARIANTS.primary;
  const scaleAnim = useRef(new Animated.Value(1)).current;

  const handlePressIn = () => {
    Animated.spring(scaleAnim, {
      toValue: 0.95,
      useNativeDriver: true,
    }).start();
  };

  const handlePressOut = () => {
    Animated.spring(scaleAnim, {
      toValue: 1,
      useNativeDriver: true,
    }).start();
  };

  return (
    <Animated.View style={{ transform: [{ scale: scaleAnim }] }}>
      <Pressable
        style={[
          styles.base,
          { backgroundColor: v.bg, borderColor: v.border },
          variant !== 'outline' && colors.shadows.small,
          (disabled || loading) && styles.disabled,
          style,
        ]}
        onPress={onPress}
        onPressIn={handlePressIn}
        onPressOut={handlePressOut}
        disabled={disabled || loading}
      >
        {loading ? (
          <ActivityIndicator color={v.text} />
        ) : (
          <>
            {icon && <Ionicons name={icon} size={20} color={v.text} style={styles.icon} />}
            <Text style={[styles.text, typography.button, { color: v.text }]}>{title}</Text>
          </>
        )}
      </Pressable>
    </Animated.View>
  );
}

const styles = StyleSheet.create({
  base: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    borderWidth: 1,
    borderRadius: 24, // Friendly pill-like shapes
    paddingVertical: 16, // Taller touch target
    paddingHorizontal: spacing.xl,
  },
  disabled: { opacity: 0.55 },
  icon: { marginRight: spacing.sm },
  text: {},
});
