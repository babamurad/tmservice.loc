import { useEffect, useState } from 'react';
import {
  View,
  Text,
  Image,
  FlatList,
  TouchableOpacity,
  TextInput,
  StyleSheet,
  ActivityIndicator,
  Linking,
  Alert,
} from 'react-native';
import { api } from '../../api/client';
import { useAuth } from '../../hooks/useAuth';
import { renderStars } from '../../utils/rating';

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
      <View style={styles.center}>
        <ActivityIndicator size="large" />
      </View>
    );
  }

  if (!detail) {
    return (
      <View style={styles.center}>
        <Text>Ошибка загрузки</Text>
      </View>
    );
  }

  return (
    <FlatList
      data={detail.portfolio_images}
      contentContainerStyle={styles.container}
      ListHeaderComponent={
        <>
          <View style={styles.header}>
            <View style={styles.avatar}>
              <Text style={styles.avatarText}>
                {(detail.user?.phone || 'M')[0].toUpperCase()}
              </Text>
            </View>
            <View style={styles.headerInfo}>
              <Text style={styles.name}>{detail.user?.phone}</Text>
              <View style={styles.statusRow}>
                <View
                  style={[
                    styles.dot,
                    { backgroundColor: detail.is_free ? '#34C759' : '#FF3B30' },
                  ]}
                />
                <Text style={styles.statusText}>
                  {detail.is_free ? 'Свободен' : 'Занят'}
                </Text>
              </View>
              {detail.reviews_count > 0 && (
                <Text style={styles.rating}>
                  {renderStars(detail.avg_rating)} {detail.avg_rating} ({detail.reviews_count})
                </Text>
              )}
              {detail.city && <Text style={styles.meta}>📍 {detail.city.name_ru}</Text>}
              {detail.category && (
                <Text style={styles.meta}>📋 {detail.category.name_ru}</Text>
              )}
            </View>
          </View>

          {detail.bio && (
            <View style={styles.section}>
              <Text style={styles.sectionTitle}>О себе</Text>
              <Text style={styles.bio}>{detail.bio}</Text>
            </View>
          )}

          {detail.portfolio_images?.length > 0 && (
            <View style={styles.section}>
              <Text style={styles.sectionTitle}>Портфолио</Text>
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
          <TouchableOpacity style={styles.callButton} onPress={handleCall}>
            <Text style={styles.callButtonText}>Позвонить</Text>
          </TouchableOpacity>

          <View style={styles.section}>
            <Text style={styles.sectionTitle}>Отзывы</Text>

            {reviewsLoading ? (
              <ActivityIndicator />
            ) : reviews.length === 0 ? (
              <Text style={styles.meta}>Пока нет отзывов</Text>
            ) : (
              reviews.map((review) => (
                <View key={review.id} style={styles.reviewItem}>
                  <Text style={styles.rating}>{renderStars(review.rating)}</Text>
                  {review.comment && <Text style={styles.bio}>{review.comment}</Text>}
                </View>
              ))
            )}

            {canReview && (
              <View style={styles.reviewForm}>
                <Text style={styles.sectionTitle}>Оставить отзыв</Text>
                <View style={styles.starPicker}>
                  {[1, 2, 3, 4, 5].map((value) => (
                    <TouchableOpacity key={value} onPress={() => setRatingInput(value)}>
                      <Text style={styles.starPickerIcon}>
                        {value <= ratingInput ? '★' : '☆'}
                      </Text>
                    </TouchableOpacity>
                  ))}
                </View>
                <TextInput
                  style={styles.commentInput}
                  placeholder="Комментарий (необязательно)"
                  value={commentInput}
                  onChangeText={setCommentInput}
                  multiline
                  numberOfLines={3}
                />
                <TouchableOpacity
                  style={styles.submitReviewButton}
                  onPress={submitReview}
                  disabled={submitting}
                >
                  {submitting ? (
                    <ActivityIndicator color="#fff" />
                  ) : (
                    <Text style={styles.submitReviewButtonText}>Отправить</Text>
                  )}
                </TouchableOpacity>
              </View>
            )}
          </View>
        </>
      }
    />
  );
}

const styles = StyleSheet.create({
  center: { flex: 1, justifyContent: 'center', alignItems: 'center' },
  container: { padding: 16 },
  header: { flexDirection: 'row', marginBottom: 20 },
  avatar: {
    width: 64,
    height: 64,
    borderRadius: 32,
    backgroundColor: '#007AFF',
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 16,
  },
  avatarText: { fontSize: 24, color: '#fff', fontWeight: '700' },
  headerInfo: { flex: 1, justifyContent: 'center' },
  name: { fontSize: 20, fontWeight: '700', marginBottom: 4 },
  statusRow: { flexDirection: 'row', alignItems: 'center', marginBottom: 4, gap: 6 },
  dot: { width: 10, height: 10, borderRadius: 5 },
  statusText: { fontSize: 14, color: '#666' },
  rating: { fontSize: 14, color: '#f5a623', marginBottom: 4 },
  meta: { fontSize: 13, color: '#999', marginTop: 2 },
  section: { marginBottom: 20 },
  sectionTitle: { fontSize: 18, fontWeight: '600', marginBottom: 8 },
  bio: { fontSize: 15, color: '#333', lineHeight: 22 },
  portfolioImage: {
    width: '100%',
    height: 200,
    borderRadius: 12,
    marginBottom: 12,
  },
  callButton: {
    backgroundColor: '#34C759',
    padding: 18,
    borderRadius: 12,
    alignItems: 'center',
    marginTop: 12,
    marginBottom: 30,
  },
  callButtonText: { color: '#fff', fontSize: 18, fontWeight: '700' },
  reviewItem: {
    borderBottomWidth: 1,
    borderBottomColor: '#eee',
    paddingBottom: 10,
    marginBottom: 10,
  },
  reviewForm: {
    marginTop: 12,
    padding: 12,
    backgroundColor: '#f5f5f5',
    borderRadius: 12,
  },
  starPicker: { flexDirection: 'row', gap: 6, marginBottom: 10 },
  starPickerIcon: { fontSize: 28, color: '#f5a623' },
  commentInput: {
    borderWidth: 1,
    borderColor: '#ddd',
    borderRadius: 8,
    padding: 10,
    backgroundColor: '#fff',
    fontSize: 14,
    marginBottom: 10,
    textAlignVertical: 'top',
  },
  submitReviewButton: {
    backgroundColor: '#007AFF',
    padding: 12,
    borderRadius: 10,
    alignItems: 'center',
  },
  submitReviewButtonText: { color: '#fff', fontSize: 15, fontWeight: '600' },
});
