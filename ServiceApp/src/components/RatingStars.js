import { View, TouchableOpacity, Text, StyleSheet } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { colors, spacing, typography } from '../theme';

export default function RatingStars({
  rating = 0,
  reviewsCount,
  size = 15,
  interactive = false,
  onChange,
}) {
  const rounded = Math.round(rating);

  return (
    <View style={styles.row}>
      {[1, 2, 3, 4, 5].map((value) => {
        const filled = interactive ? value <= rating : value <= rounded;
        const Wrapper = interactive ? TouchableOpacity : View;

        return (
          <Wrapper key={value} onPress={interactive ? () => onChange?.(value) : undefined}>
            <Ionicons
              name={filled ? 'star' : 'star-outline'}
              size={interactive ? size + 8 : size}
              color={colors.rating}
              style={interactive ? styles.interactiveStar : undefined}
            />
          </Wrapper>
        );
      })}
      {!interactive && reviewsCount > 0 && (
        <Text style={[typography.caption, styles.count]}>({reviewsCount})</Text>
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  row: { flexDirection: 'row', alignItems: 'center', gap: 2 },
  interactiveStar: { marginRight: spacing.xs },
  count: { marginLeft: spacing.xs },
});
