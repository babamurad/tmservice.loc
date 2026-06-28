import { useState, useEffect } from 'react';
import {
  View,
  Text,
  Image,
  StyleSheet,
  ActivityIndicator,
} from 'react-native';
import { api } from '../../api/client';

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
      <View style={styles.center}>
        <ActivityIndicator size="large" />
      </View>
    );
  }

  return (
    <View style={styles.container}>
      <Text style={styles.hint}>Отсканируйте QR-код, чтобы открыть профиль</Text>
      {qrUrl && (
        <Image
          source={{ uri: qrUrl }}
          style={styles.qrImage}
          resizeMode="contain"
        />
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  center: { flex: 1, justifyContent: 'center', alignItems: 'center' },
  container: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#fff',
    padding: 40,
  },
  hint: { fontSize: 16, color: '#666', marginBottom: 30, textAlign: 'center' },
  qrImage: { width: 280, height: 280 },
});
