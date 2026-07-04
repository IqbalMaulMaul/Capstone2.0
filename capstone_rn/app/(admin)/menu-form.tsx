import React, { useEffect, useState } from 'react';
import { View, Text, StyleSheet, TextInput, TouchableOpacity, ScrollView, Image, ActivityIndicator, Alert, KeyboardAvoidingView, Platform } from 'react-native';
import { useLocalSearchParams, useRouter } from 'expo-router';
import * as ImagePicker from 'expo-image-picker';
import { MaterialIcons } from '@expo/vector-icons';
import { Colors, Spacing, BorderRadius, FontSize, Shadows } from '../../constants/Colors';
import { adminService } from '../../services/admin';
import { Category, MenuFormData } from '../../types';

export default function MenuFormScreen() {
  const params = useLocalSearchParams();
  const router = useRouter();
  const isEditing = !!params.id;

  const [categories, setCategories] = useState<Category[]>([]);
  const [loading, setLoading] = useState(false);
  const [initialLoading, setInitialLoading] = useState(true);

  const [name, setName] = useState('');
  const [description, setDescription] = useState('');
  const [price, setPrice] = useState('');
  const [categoryId, setCategoryId] = useState<number | null>(null);
  const [isAvailable, setIsAvailable] = useState(true);
  const [image, setImage] = useState<any>(null);
  const [existingImageUrl, setExistingImageUrl] = useState<string | null>(null);

  useEffect(() => {
    const init = async () => {
      try {
        const cats = await adminService.getCategories();
        setCategories(cats);

        if (isEditing) {
          // Fetch existing menu details from the list or separate endpoint if we had one
          const menus = await adminService.getMenus();
          const menu = menus.find(m => m.id === Number(params.id));
          if (menu) {
            setName(menu.name);
            setDescription(menu.description || '');
            setPrice(menu.price.toString());
            setCategoryId(menu.category_id);
            setIsAvailable(menu.is_available);
            setExistingImageUrl(menu.image_url);
          }
        }
      } catch (e: any) {
        Alert.alert('Error', 'Gagal memuat data');
      } finally {
        setInitialLoading(false);
      }
    };
    init();
  }, [params.id]);

  const pickImage = async () => {
    const result = await ImagePicker.launchImageLibraryAsync({
      mediaTypes: ImagePicker.MediaTypeOptions.Images,
      allowsEditing: true,
      aspect: [1, 1],
      quality: 0.8,
    });

    if (!result.canceled) {
      setImage(result.assets[0]);
    }
  };

  const handleSave = async () => {
    if (!name.trim() || !price || !categoryId) {
      Alert.alert('Error', 'Nama, harga, dan kategori wajib diisi');
      return;
    }

    setLoading(true);
    try {
      const data: MenuFormData = {
        name: name.trim(),
        description: description.trim(),
        price: Number(price),
        category_id: categoryId,
        is_available: isAvailable,
        image: image,
      };

      if (isEditing) {
        await adminService.updateMenu(Number(params.id), data);
        Alert.alert('Sukses', 'Menu berhasil diupdate', [{ text: 'OK', onPress: () => router.back() }]);
      } else {
        await adminService.createMenu(data);
        Alert.alert('Sukses', 'Menu berhasil ditambahkan', [{ text: 'OK', onPress: () => router.back() }]);
      }
    } catch (e: any) {
      Alert.alert('Error', e.message || 'Gagal menyimpan menu');
    } finally {
      setLoading(false);
    }
  };

  if (initialLoading) {
    return (
      <View style={styles.center}>
        <ActivityIndicator size="large" color={Colors.primary} />
      </View>
    );
  }

  return (
    <KeyboardAvoidingView 
      style={styles.container} 
      behavior={Platform.OS === 'ios' ? 'padding' : undefined}
    >
      <View style={styles.header}>
        <TouchableOpacity onPress={() => router.back()} style={styles.backBtn}>
          <MaterialIcons name="arrow-back" size={24} color={Colors.text} />
        </TouchableOpacity>
        <Text style={styles.title}>{isEditing ? 'Edit Menu' : 'Tambah Menu Baru'}</Text>
        <View style={{ width: 24 }} />
      </View>

      <ScrollView contentContainerStyle={styles.scroll}>
        {/* Image Picker */}
        <TouchableOpacity style={styles.imagePicker} onPress={pickImage}>
          {image ? (
            <Image source={{ uri: image.uri }} style={styles.imagePreview} />
          ) : existingImageUrl ? (
            <Image source={{ uri: existingImageUrl }} style={styles.imagePreview} />
          ) : (
            <View style={styles.imagePlaceholder}>
              <MaterialIcons name="add-a-photo" size={32} color={Colors.textMuted} />
              <Text style={styles.imageText}>Pilih Foto Menu</Text>
            </View>
          )}
        </TouchableOpacity>

        {/* Form Fields */}
        <View style={styles.formGroup}>
          <Text style={styles.label}>Nama Menu</Text>
          <TextInput
            style={styles.input}
            placeholder="Misal: Nasi Goreng Spesial"
            placeholderTextColor={Colors.textMuted}
            value={name}
            onChangeText={setName}
          />
        </View>

        <View style={styles.formGroup}>
          <Text style={styles.label}>Kategori</Text>
          <View style={styles.categories}>
            {categories.map(cat => (
              <TouchableOpacity
                key={cat.id}
                style={[styles.categoryChip, categoryId === cat.id && styles.categoryChipActive]}
                onPress={() => setCategoryId(cat.id)}
              >
                <Text style={[styles.categoryText, categoryId === cat.id && styles.categoryTextActive]}>
                  {cat.name}
                </Text>
              </TouchableOpacity>
            ))}
          </View>
        </View>

        <View style={styles.formGroup}>
          <Text style={styles.label}>Harga (Rp)</Text>
          <TextInput
            style={styles.input}
            placeholder="Misal: 25000"
            placeholderTextColor={Colors.textMuted}
            keyboardType="numeric"
            value={price}
            onChangeText={setPrice}
          />
        </View>

        <View style={styles.formGroup}>
          <Text style={styles.label}>Deskripsi (Opsional)</Text>
          <TextInput
            style={[styles.input, styles.textArea]}
            placeholder="Deskripsi singkat..."
            placeholderTextColor={Colors.textMuted}
            multiline
            numberOfLines={3}
            value={description}
            onChangeText={setDescription}
          />
        </View>

        <View style={styles.switchGroup}>
          <Text style={styles.label}>Tersedia (Ready Stock)</Text>
          <TouchableOpacity 
            style={[styles.switch, isAvailable ? styles.switchOn : styles.switchOff]}
            onPress={() => setIsAvailable(!isAvailable)}
          >
            <View style={[styles.switchThumb, isAvailable ? styles.switchThumbOn : styles.switchThumbOff]} />
          </TouchableOpacity>
        </View>
      </ScrollView>

      <View style={styles.footer}>
        <TouchableOpacity style={styles.saveBtn} onPress={handleSave} disabled={loading}>
          {loading ? (
            <ActivityIndicator color={Colors.white} />
          ) : (
            <Text style={styles.saveBtnText}>Simpan Menu</Text>
          )}
        </TouchableOpacity>
      </View>
    </KeyboardAvoidingView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: Colors.background,
  },
  center: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: Colors.background,
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    padding: Spacing.lg,
    backgroundColor: Colors.surface,
    borderBottomWidth: 1,
    borderBottomColor: Colors.border,
    paddingTop: Platform.OS === 'ios' ? 60 : Spacing.lg,
  },
  backBtn: {
    padding: Spacing.sm,
  },
  title: {
    fontSize: FontSize.lg,
    fontWeight: 'bold',
    color: Colors.text,
  },
  scroll: {
    padding: Spacing.lg,
  },
  imagePicker: {
    alignSelf: 'center',
    marginBottom: Spacing.xl,
  },
  imagePreview: {
    width: 120,
    height: 120,
    borderRadius: BorderRadius.xl,
  },
  imagePlaceholder: {
    width: 120,
    height: 120,
    borderRadius: BorderRadius.xl,
    backgroundColor: Colors.surfaceLight,
    borderWidth: 2,
    borderColor: Colors.border,
    borderStyle: 'dashed',
    justifyContent: 'center',
    alignItems: 'center',
  },
  imageText: {
    color: Colors.textMuted,
    fontSize: FontSize.xs,
    marginTop: Spacing.sm,
  },
  formGroup: {
    marginBottom: Spacing.lg,
  },
  label: {
    fontSize: FontSize.sm,
    fontWeight: '600',
    color: Colors.textSecondary,
    marginBottom: Spacing.sm,
  },
  input: {
    backgroundColor: Colors.surface,
    borderWidth: 1,
    borderColor: Colors.border,
    borderRadius: BorderRadius.md,
    padding: Spacing.md,
    color: Colors.text,
    fontSize: FontSize.md,
  },
  textArea: {
    height: 100,
    textAlignVertical: 'top',
  },
  categories: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: Spacing.sm,
  },
  categoryChip: {
    paddingHorizontal: Spacing.md,
    paddingVertical: Spacing.sm,
    borderRadius: BorderRadius.full,
    backgroundColor: Colors.surface,
    borderWidth: 1,
    borderColor: Colors.border,
  },
  categoryChipActive: {
    backgroundColor: Colors.primary,
    borderColor: Colors.primary,
  },
  categoryText: {
    color: Colors.textSecondary,
    fontSize: FontSize.sm,
    fontWeight: '600',
  },
  categoryTextActive: {
    color: Colors.white,
  },
  switchGroup: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: Spacing.xl,
  },
  switch: {
    width: 50,
    height: 28,
    borderRadius: 14,
    justifyContent: 'center',
    padding: 2,
  },
  switchOn: {
    backgroundColor: Colors.primary,
  },
  switchOff: {
    backgroundColor: Colors.surfaceLight,
  },
  switchThumb: {
    width: 24,
    height: 24,
    borderRadius: 12,
    backgroundColor: Colors.white,
  },
  switchThumbOn: {
    alignSelf: 'flex-end',
  },
  switchThumbOff: {
    alignSelf: 'flex-start',
  },
  footer: {
    padding: Spacing.lg,
    backgroundColor: Colors.surface,
    borderTopWidth: 1,
    borderTopColor: Colors.border,
    paddingBottom: Platform.OS === 'ios' ? 32 : Spacing.lg,
  },
  saveBtn: {
    backgroundColor: Colors.primary,
    height: 52,
    borderRadius: BorderRadius.md,
    justifyContent: 'center',
    alignItems: 'center',
    ...Shadows.glow(Colors.primary),
  },
  saveBtnText: {
    color: Colors.white,
    fontSize: FontSize.lg,
    fontWeight: 'bold',
  },
});
