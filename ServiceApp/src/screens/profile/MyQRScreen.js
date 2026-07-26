import { useState, useEffect } from 'react';
import { View, Text, Image, StyleSheet, ActivityIndicator } from 'react-native';
import { api } from '../../api/client';
import ScreenContainer from '../../components/ScreenContainer';
import Card from '../../components/Card';
import { colors, spacing, typography } from '../../theme';

export default function MyQRScreen({ route }) {
  const { profile } = route.params;
  const [qrUrl, setQrUrl] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    generateQR();
  }, []);

  async function generateQR() {
    try {
      if (profile.qr_code_path) {
        setQrUrl(
          `https://tmservice.loc/storage/${profile.qr_code_path}`,
        );
        setLoading(false);
        return;
      }
      const data = await api('/profile/qr', { method: 'POST' });
      setQrUrl(data.qr_code_url);
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  }

  if (loading) {
    return (
      <ScreenContainer style={styles.center}>
        <ActivityIndicator size="large" color={colors.primary} />
      </ScreenContainer>
    );
  }

  return (
    <ScreenContainer style={styles.center}>
      <Text style={[typography.bodyMuted, styles.hint]}>
        Отсканируйте QR-код, чтобы открыть профиль
      </Text>
      {qrUrl && (
        <Card style={styles.qrCard}>
          <Image source={{ uri: qrUrl }} style={styles.qrImage} resizeMode="contain" />
        </Card>
      )}
    </ScreenContainer>
  );
}

const styles = StyleSheet.create({
  center: { justifyContent: 'center', alignItems: 'center', padding: spacing.xl },
  hint: { marginBottom: spacing.xl, textAlign: 'center' },
  qrCard: { padding: spacing.xl },
  qrImage: { width: 240, height: 240 },
});
