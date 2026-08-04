import { useEffect, useState } from 'react';
import { View, Text, Image, ScrollView, FlatList, StyleSheet, ActivityIndicator, Linking, Alert } from 'react-native';
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
  // route.params.master (полный объект) приходит при переходе из списка/поиска;
  // route.params.id — из диплинка (QR/`/m/{id}`), где известен только id.
  const masterId = route.params.id ?? route.params.master.id;
  const { user } = useAuth();
  const [detail, setDetail] = useState(null);
  const [loading, setLoading] = useState(true);
  const [reviews, setReviews] = useState([]);
  const [reviewsLoading, setReviewsLoading] = useState(true);
  const [ratingInput, setRatingInput] = useState(0);
  const [commentInput, setCommentInput] = useState('');
  const [submitting, setSubmitting] = useState(false);
  const [showReportForm, setShowReportForm] = useState(false);
  const [reportReason, setReportReason] = useState('');
  const [reportSubmitting, setReportSubmitting] = useState(false);

  const canReview = user?.role === 'client' && !!user?.phone_verified_at;
  const canReport = !!user?.phone_verified_at && user?.id !== detail?.user?.id;

  useEffect(() => {
    loadDetail();
    loadReviews();
  }, []);

  async function loadDetail() {
    try {
      const data = await api(`/masters/${masterId}`);
      setDetail(data);
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  }

  async function loadReviews() {
    try {
      const data = await api(`/masters/${masterId}/reviews`);
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
      await api(`/masters/${masterId}/reviews`, {
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

  async function submitReport() {
    if (!reportReason.trim()) {
      Alert.alert('Жалоба', 'Опишите, в чём проблема');
      return;
    }

    setReportSubmitting(true);
    try {
      await api(`/masters/${masterId}/reports`, {
        method: 'POST',
        body: JSON.stringify({ reason: reportReason }),
      });
      setReportReason('');
      setShowReportForm(false);
      Alert.alert('Спасибо', 'Жалоба отправлена, мы её рассмотрим');
    } catch (err) {
      Alert.alert('Ошибка', err?.message || 'Не удалось отправить жалобу');
    } finally {
      setReportSubmitting(false);
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
    <ScrollView style={styles.scroll} contentContainerStyle={styles.scrollContent}>
      {/* Decorative Cover */}
      <View style={styles.cover}>
        <View style={styles.coverPattern} />
      </View>

      <View style={styles.container}>
        <View style={styles.header}>
          <View style={styles.avatarWrapper}>
            <Avatar phone={detail.user?.phone} size={80} />
          </View>
          <View style={styles.headerInfo}>
            <Text style={typography.title}>{detail.user?.phone}</Text>
            <View style={styles.statusRow}>
              <StatusBadge isFree={detail.is_free} />
            </View>
            {detail.reviews_count > 0 && (
              <RatingStars rating={detail.avg_rating} reviewsCount={detail.reviews_count} />
            )}
            <View style={styles.tags}>
              {detail.city && <Text style={typography.caption}>📍 {detail.city.name_ru}</Text>}
              {detail.category && <Text style={typography.caption}>📋 {detail.category.name_ru}</Text>}
            </View>
          </View>
        </View>

        <Button title="Связаться с мастером" icon="call" variant="primary" onPress={handleCall} style={styles.callButton} />

        {detail.bio && (
          <View style={styles.section}>
            <Text style={[typography.heading, styles.sectionTitle]}>О себе</Text>
            <Text style={typography.body}>{detail.bio}</Text>
          </View>
        )}

        {detail.portfolio_images?.length > 0 && (
          <View style={styles.section}>
            <Text style={[typography.heading, styles.sectionTitle]}>Портфолио</Text>
            <FlatList
              horizontal
              showsHorizontalScrollIndicator={false}
              data={detail.portfolio_images}
              keyExtractor={(item) => String(item.id)}
              renderItem={({ item }) => (
                <Image
                  source={{ uri: `https://tmservice.loc/storage/${item.image_path}` }}
                  style={styles.portfolioImage}
                  resizeMode="cover"
                />
              )}
              contentContainerStyle={styles.portfolioContainer}
            />
          </View>
        )}

        <View style={styles.section}>
          <Text style={[typography.heading, styles.sectionTitle]}>Отзывы</Text>

          {reviewsLoading ? (
            <ActivityIndicator color={colors.primary} style={{ marginTop: spacing.md }} />
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
              <RatingStars rating={ratingInput} interactive onChange={setRatingInput} size={22} />
              <TextField
                style={styles.commentInput}
                placeholder="Ваш комментарий (необязательно)"
                value={commentInput}
                onChangeText={setCommentInput}
                multiline
                numberOfLines={3}
              />
              <Button title="Отправить" icon="send" onPress={submitReview} loading={submitting} />
            </Card>
          )}
        </View>

        {canReport && (
          <View style={styles.section}>
            {showReportForm ? (
              <Card style={styles.reviewForm}>
                <Text style={[typography.heading, styles.sectionTitle]}>Пожаловаться на мастера</Text>
                <TextField
                  style={styles.commentInput}
                  placeholder="Опишите проблему"
                  value={reportReason}
                  onChangeText={setReportReason}
                  multiline
                  numberOfLines={3}
                />
                <Button title="Отправить жалобу" icon="flag" onPress={submitReport} loading={reportSubmitting} />
              </Card>
            ) : (
              <Button
                title="Пожаловаться на мастера"
                icon="flag-outline"
                variant="outline"
                onPress={() => setShowReportForm(true)}
              />
            )}
          </View>
        )}
      </View>
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  center: { flex: 1, justifyContent: 'center', alignItems: 'center', backgroundColor: colors.bg },
  scroll: { flex: 1, backgroundColor: colors.bg },
  scrollContent: { paddingBottom: spacing.xxl },
  cover: {
    height: 120,
    backgroundColor: colors.primary,
    borderBottomLeftRadius: 32,
    borderBottomRightRadius: 32,
    overflow: 'hidden',
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
  },
  coverPattern: {
    flex: 1,
    opacity: 0.1,
    backgroundColor: colors.primaryDark,
    // A placeholder for a background pattern image
  },
  container: {
    padding: spacing.lg,
    paddingTop: 80, // Offset for cover overlap
  },
  header: {
    flexDirection: 'row',
    marginBottom: spacing.lg,
    gap: spacing.lg,
    alignItems: 'flex-end',
  },
  avatarWrapper: {
    padding: 4,
    backgroundColor: colors.bg,
    borderRadius: 50,
  },
  headerInfo: {
    flex: 1,
    justifyContent: 'center',
    gap: spacing.xs,
    paddingBottom: spacing.sm,
  },
  statusRow: { marginVertical: 2 },
  tags: { flexDirection: 'row', gap: spacing.md, marginTop: 4, flexWrap: 'wrap' },
  section: { marginBottom: spacing.xl },
  sectionTitle: { marginBottom: spacing.md },
  portfolioContainer: { gap: spacing.md, paddingRight: spacing.lg },
  portfolioImage: {
    width: 280,
    height: 200,
    borderRadius: 20,
  },
  callButton: { marginBottom: spacing.xl },
  reviewItem: { marginBottom: spacing.sm, gap: spacing.xs },
  reviewComment: { marginTop: spacing.xs },
  reviewForm: { marginTop: spacing.lg, backgroundColor: colors.surface, padding: spacing.xl },
  commentInput: { marginTop: spacing.md, minHeight: 80, textAlignVertical: 'top' },
});
