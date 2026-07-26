import { useEffect, useState } from 'react';
import { View, Text, Image, FlatList, StyleSheet, ActivityIndicator, Linking, Alert } from 'react-native';
import { api } from '../../api/client';
import { useAuth } from '../../hooks/useAuth';
import ScreenContainer from '../../components/ScreenContainer';
import Card from '../../components/Card';
import Avatar from '../../components/Avatar';
import StatusBadge from '../../components/StatusBadge';
import RatingStars from '../../components/RatingStars';
import Button from '../../components/Button';
import TextField from '../../components/TextField';
import { colors, spacing, typography } from '../../theme';

export default function MasterDetailScreen({ route }) {
  const { master } = route.params;
  const { user } = useAuth();
  const [detail, setDetail] = useState(null);
  const [loading, setLoading] = useState(true);
  const [reviews, setReviews] = useState([]);
  const [reviewsLoading, setReviewsLoading] = useState(true);
  const [ratingInput, setRatingInput] = useState(0);
  const [commentInput, setCommentInput] = useState('');
  const [submitting, setSubmitting] = useState(false);

  const canReview = user?.role === 'client' && !!user?.phone_verified_at;

  useEffect(() => {
    loadDetail();
    loadReviews();
  }, []);

  async function loadDetail() {
    try {
      const data = await api(`/masters/${master.id}`);
      setDetail(data);
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  }

  async function loadReviews() {
    try {
      const data = await api(`/masters/${master.id}/reviews`);
      setReviews(data.data);
    } catch (err) {
      console.error(err);
    } finally {
      setReviewsLoading(false);
    }
  }

  async function submitReview() {
    if (ratingInput === 0) {
      Alert.alert('Оценка', 'Выберите оценку от 1 до 5 звёзд');
      return;
    }

    setSubmitting(true);
    try {
      await api(`/masters/${master.id}/reviews`, {
        method: 'POST',
        body: JSON.stringify({ rating: ratingInput, comment: commentInput || null }),
      });
      setRatingInput(0);
      setCommentInput('');
      Alert.alert('Спасибо', 'Отзыв отправлен на модерацию');
    } catch (err) {
      Alert.alert('Ошибка', err?.message || 'Не удалось отправить отзыв');
    } finally {
      setSubmitting(false);
    }
  }

  function handleCall() {
    const phone = detail?.user?.phone;
    if (phone) {
      Linking.openURL(`tel:${phone}`);
    }
  }

  if (loading) {
    return (
      <ScreenContainer style={styles.center}>
        <ActivityIndicator size="large" color={colors.primary} />
      </ScreenContainer>
    );
  }

  if (!detail) {
    return (
      <ScreenContainer style={styles.center}>
        <Text style={typography.body}>Ошибка загрузки</Text>
      </ScreenContainer>
    );
  }

  return (
    <ScreenContainer>
      <FlatList
        data={detail.portfolio_images}
        contentContainerStyle={styles.container}
        ListHeaderComponent={
          <>
            <View style={styles.header}>
              <Avatar phone={detail.user?.phone} size={64} />
              <View style={styles.headerInfo}>
                <Text style={typography.title}>{detail.user?.phone}</Text>
                <View style={styles.statusRow}>
                  <StatusBadge isFree={detail.is_free} />
                </View>
                {detail.reviews_count > 0 && (
                  <RatingStars rating={detail.avg_rating} reviewsCount={detail.reviews_count} />
                )}
                {detail.city && <Text style={typography.caption}>📍 {detail.city.name_ru}</Text>}
                {detail.category && <Text style={typography.caption}>📋 {detail.category.name_ru}</Text>}
              </View>
            </View>

            {detail.bio && (
              <View style={styles.section}>
                <Text style={[typography.heading, styles.sectionTitle]}>О себе</Text>
                <Text style={typography.body}>{detail.bio}</Text>
              </View>
            )}

            {detail.portfolio_images?.length > 0 && (
              <View style={styles.section}>
                <Text style={[typography.heading, styles.sectionTitle]}>Портфолио</Text>
              </View>
            )}
          </>
        }
        renderItem={({ item }) => (
          <Image
            source={{ uri: `https://tmservice.loc/storage/${item.image_path}` }}
            style={styles.portfolioImage}
            resizeMode="cover"
          />
        )}
        keyExtractor={(item) => String(item.id)}
        ListFooterComponent={
          <>
            <Button title="Позвонить" icon="call" variant="success" onPress={handleCall} style={styles.callButton} />

            <View style={styles.section}>
              <Text style={[typography.heading, styles.sectionTitle]}>Отзывы</Text>

              {reviewsLoading ? (
                <ActivityIndicator color={colors.primary} />
              ) : reviews.length === 0 ? (
                <Text style={typography.bodyMuted}>Пока нет отзывов</Text>
              ) : (
                reviews.map((review) => (
                  <Card key={review.id} style={styles.reviewItem}>
                    <RatingStars rating={review.rating} size={14} />
                    {review.comment && (
                      <Text style={[typography.body, styles.reviewComment]}>{review.comment}</Text>
                    )}
                  </Card>
                ))
              )}

              {canReview && (
                <Card style={styles.reviewForm}>
                  <Text style={[typography.heading, styles.sectionTitle]}>Оставить отзыв</Text>
                  <RatingStars rating={ratingInput} interactive onChange={setRatingInput} size={18} />
                  <TextField
                    style={styles.commentInput}
                    placeholder="Комментарий (необязательно)"
                    value={commentInput}
                    onChangeText={setCommentInput}
                    multiline
                    numberOfLines={3}
                  />
                  <Button title="Отправить" icon="send" onPress={submitReview} loading={submitting} />
                </Card>
              )}
            </View>
          </>
        }
      />
    </ScreenContainer>
  );
}

const styles = StyleSheet.create({
  center: { justifyContent: 'center', alignItems: 'center' },
  container: { padding: spacing.lg },
  header: { flexDirection: 'row', marginBottom: spacing.xl, gap: spacing.lg },
  headerInfo: { flex: 1, justifyContent: 'center', gap: spacing.xs },
  statusRow: { marginVertical: 2 },
  section: { marginBottom: spacing.xl },
  sectionTitle: { marginBottom: spacing.md },
  portfolioImage: {
    width: '100%',
    height: 200,
    borderRadius: 12,
    marginBottom: spacing.md,
  },
  callButton: { marginTop: spacing.md, marginBottom: spacing.xxl },
  reviewItem: { marginBottom: spacing.sm, gap: spacing.xs },
  reviewComment: { marginTop: spacing.xs },
  reviewForm: { marginTop: spacing.md },
  commentInput: { marginTop: spacing.md, minHeight: 70, textAlignVertical: 'top' },
});
