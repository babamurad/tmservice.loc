import { View, Text, StyleSheet } from 'react-native';
import { colors } from '../theme';

export default function Avatar({ phone, size = 56 }) {
  const letter = (phone || 'M').replace('+', '')[0]?.toUpperCase() || 'M';

  return (
    <View
      style={[
        styles.circle,
        { width: size, height: size, borderRadius: size / 2 },
      ]}
    >
      <Text style={[styles.letter, { fontSize: size * 0.4 }]}>{letter}</Text>
    </View>
  );
}

const styles = StyleSheet.create({
  circle: {
    backgroundColor: colors.primary,
    alignItems: 'center',
    justifyContent: 'center',
  },
  letter: { color: colors.white, fontWeight: '700' },
});
