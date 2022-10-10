import { useParams, useNavigate } from "react-router-native";
import {
  Text,
  View,
  Button,
  StyleSheet,
  SafeAreaView,
  ActivityIndicator,
  Modal,
} from "react-native";

import { useEffect, useState, useContext } from "react";
import { Ionicons } from "@expo/vector-icons";
// context
import ThemeOptions from "../services/ThemeOptions";
// theme options
import { ThemeStyleColors } from "../config/Theme";
// services
import { deleteDb } from "../services/Store";

export default function Delete() {
  // params
  const params = useParams();
  // navigate
  const navigate = useNavigate();
  // context
  const config = useContext(ThemeOptions);
  // loaded
  const [loaded, setLoaded] = useState(false);
  // modal
  const [isVisible, setIsVisible] = useState(false);
  const [msg, setMsg] = useState("");

  // delete uid
  const handleDeleteUid = async () => {
    setLoaded(false);
    try {
      const response = await deleteDb("snippets", params.id);
      setIsVisible(true);
      setMsg(response);
    } catch (e) {
      console.log(e);
    } finally {
      setLoaded(true);
    }
  };

  useEffect(() => setLoaded(true), []);

  if (!loaded) {
    return (
      <View style={[styles.container, styles.horizontal]}>
        <ActivityIndicator size="large" color={ThemeStyleColors.warning} />
      </View>
    );
  }

  return (
    loaded && (
      <SafeAreaView style={styles.container}>
        <Modal
          transparent
          animationType={"fade"}
          onRequestClose={() => setIsVisible(false)}
          visible={isVisible}
        >
          <View style={styles.modal}>
            <Text style={styles.modalTitle}>{msg}</Text>
            <View style={styles.buttons}>
              <Button
                color={ThemeStyleColors.primary}
                title={config.lang.backToHome}
                onPress={() => navigate("/")}
              />
              <Button
                color={ThemeStyleColors.danger}
                title={config.lang.cancel}
                onPress={() => setIsVisible(false)}
              />
            </View>
          </View>
        </Modal>

        <View style={styles.info}>
          <Text style={styles.title}>{config.lang.sureDelete}</Text>
          <View style={styles.infoBtn}>
            <Button
              color={ThemeStyleColors.danger}
              title={[
                <Ionicons
                  name="trash"
                  size={16}
                  color={ThemeStyleColors.warning}
                  style={{ marginRight: 5 }}
                />,
                config.lang.delete,
              ]}
              onPress={handleDeleteUid}
            />
          </View>
        </View>
      </SafeAreaView>
    )
  );
}

const styles = StyleSheet.create({
  horizontal: {
    flexDirection: "row",
    justifyContent: "space-around",
    padding: 10,
  },
  container: {
    display: "flex",
    flexDirection: "column",
    flex: 1,
  },
  info: {
    margin: 12,
  },
  infoBtn: {
    width: 100,
    margin: 12,
  },
  title: {
    margin: 12,
    fontSize: 18,
    color: ThemeStyleColors.warning,
  },
  buttons: {
    margin: 12,
    display: "flex",
    flexDirection: "row",
    justifyContent: "space-between",
    maxWidth: 150,
  },
  modal: {
    backgroundColor: ThemeStyleColors.secondary,
    borderColor: ThemeStyleColors.primary,
    borderRadius: 4,
    borderWidth: 1,
    padding: 10,
  },
  modalTitle: {
    fontSize: 18,
    margin: 12,
    color: ThemeStyleColors.warning,
  },
});
