import React, { useState, useEffect } from "react";
import { View, StyleSheet, ScrollView, TouchableOpacity, ActivityIndicator } from "react-native";
import ScreenContainer from '@/components/ScreenContainer';
import { MaterialIcons } from '@expo/vector-icons';
import Colors, { Color } from '@/constants/Colors';
import { useColorScheme } from '@/hooks/useSettings';
import { Stack, router } from 'expo-router';
import ScaledText from '@/components/ScaledText';
import * as DocumentPicker from 'expo-document-picker';
import AsyncStorage from '@react-native-async-storage/async-storage';

// Define document type
interface Document {
  id: string;
  name: string;
  type: string;
  uri: string;
  size: number;
  createdAt: number;
}

export default function Documents() {
  const colorScheme = useColorScheme();
  const [documents, setDocuments] = useState<Document[]>([]);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    loadDocuments();
  }, []);

  const loadDocuments = async () => {
    try {
      setIsLoading(true);
      const storedDocs = await AsyncStorage.getItem('user_documents');
      if (storedDocs) {
        setDocuments(JSON.parse(storedDocs));
      }
    } catch (error) {
      console.error('Error loading documents:', error);
    } finally {
      setIsLoading(false);
    }
  };

  const pickDocument = async () => {
    try {
      const result = await DocumentPicker.getDocumentAsync({
        type: '*/*',
        copyToCacheDirectory: true,
      });
      
      if (result.canceled) return;
      
      const file = result.assets[0];
      
      // Create a new document object
      const newDoc: Document = {
        id: Date.now().toString(),
        name: file.name,
        type: file.mimeType || 'application/octet-stream',
        uri: file.uri,
        size: file.size || 0,
        createdAt: Date.now(),
      };
      
      // Add to state and save to storage
      const updatedDocs = [...documents, newDoc];
      setDocuments(updatedDocs);
      await AsyncStorage.setItem('user_documents', JSON.stringify(updatedDocs));
      
    } catch (error) {
      console.error('Error picking document:', error);
    }
  };

  const formatFileSize = (bytes: number) => {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / 1048576).toFixed(1) + ' MB';
  };

  const formatDate = (timestamp: number) => {
    return new Date(timestamp).toLocaleDateString();
  };

  const openDocument = (document: Document) => {
    // Implement document opening logic
    // You may need to use WebView or a native module
    console.log('Opening document:', document);
  };

  const deleteDocument = async (id: string) => {
    try {
      const updatedDocs = documents.filter(doc => doc.id !== id);
      setDocuments(updatedDocs);
      await AsyncStorage.setItem('user_documents', JSON.stringify(updatedDocs));
    } catch (error) {
      console.error('Error deleting document:', error);
    }
  };

  return (
    <ScreenContainer>
      <Stack.Screen options={{ title: 'My Documents' }} />
      
      <View style={styles.container}>
        <View style={styles.header}>
          <ScaledText style={[
            styles.title, 
            { color: Colors[`${colorScheme}`].text }
          ]}>
            Document Vault
          </ScaledText>
          <TouchableOpacity
            style={[
              styles.addButton,
              { backgroundColor: Colors[`${colorScheme}`].primary }
            ]}
            onPress={pickDocument}
          >
            <MaterialIcons name="add" size={24} color="white" />
            <ScaledText style={styles.addButtonText}>
              Add Document
            </ScaledText>
          </TouchableOpacity>
        </View>
        
        {isLoading ? (
          <View style={styles.loadingContainer}>
            <ActivityIndicator size="large" color={Colors[`${colorScheme}`].primary} />
            <ScaledText style={{ marginTop: 12, color: Colors[`${colorScheme}`].gray400}}>
              Loading documents...
            </ScaledText>
          </View>
        ) : documents.length === 0 ? (
          <View style={styles.emptyContainer}>
            <MaterialIcons 
              name="folder-open" 
              size={64} 
              color={Colors[`${colorScheme}`].gray400} 
            />
            <ScaledText style={[
              styles.emptyText,
              { color: Colors[`${colorScheme}`].gray600 }
            ]}>
              No documents yet
            </ScaledText>
            <ScaledText style={[
              styles.emptySubtext,
              { color: Colors[`${colorScheme}`].gray500 }
            ]}>
              Add your first document by tapping the button above
            </ScaledText>
          </View>
        ) : (
          <ScrollView style={styles.documentList}>
            {documents.map(doc => (
              <TouchableOpacity
                key={doc.id}
                style={[
                  styles.documentItem,
                  { backgroundColor: Colors[`${colorScheme}`].gray800 }
                ]}
                onPress={() => openDocument(doc)}
              >
                <View style={styles.documentIcon}>
                  <MaterialIcons
                    name={doc.type.includes('pdf') ? 'picture-as-pdf' : 'insert-drive-file'}
                    size={24}
                    color={Colors[`${colorScheme}`].primary}
                  />
                </View>
                <View style={styles.documentInfo}>
                  <ScaledText style={[
                    styles.documentName, 
                    { color: Colors[`${colorScheme}`].primary }
                  ]} numberOfLines={1}>
                    {doc.name}
                  </ScaledText>
                  <ScaledText style={[
                    styles.documentMeta,
                    { color: Colors[`${colorScheme}`].gray400 }
                  ]}>
                    {formatFileSize(doc.size)} • {formatDate(doc.createdAt)}
                  </ScaledText>
                </View>
                <TouchableOpacity
                  style={styles.deleteButton}
                  onPress={() => deleteDocument(doc.id)}
                >
                  <MaterialIcons name="delete" size={22} color={Colors[`${colorScheme}`].gray500} />
                </TouchableOpacity>
              </TouchableOpacity>
            ))}
          </ScrollView>
        )}
      </View>
    </ScreenContainer>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: 16,
    marginBottom: 8,
  },
  title: {
    fontSize: 24,
    fontWeight: 'bold',
  },
  addButton: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 12,
    paddingVertical: 8,
    borderRadius: 8,
  },
  addButtonText: {
    color: 'white',
    fontWeight: '500',
    marginLeft: 4,
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  emptyContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: 24,
  },
  emptyText: {
    fontSize: 18,
    fontWeight: '500',
    marginTop: 16,
  },
  emptySubtext: {
    fontSize: 14,
    textAlign: 'center',
    marginTop: 8,
  },
  documentList: {
    flex: 1,
  },
  documentItem: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 12,
    marginHorizontal: 16,
    marginBottom: 8,
    borderRadius: 8,
    elevation: 1,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.1,
    shadowRadius: 1,
  },
  documentIcon: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: Color.gray[200],
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 12,
  },
  documentInfo: {
    flex: 1,
  },
  documentName: {
    fontSize: 16,
    fontWeight: '500',
  },
  documentMeta: {
    fontSize: 12,
    marginTop: 2,
  },
  deleteButton: {
    padding: 8,
  },
}); 